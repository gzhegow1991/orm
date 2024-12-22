<?php

namespace Gzhegow\Database\Core\Model\Traits\Relation;

use Gzhegow\Database\Exception\LogicException;
use Gzhegow\Database\Exception\RuntimeException;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\Relations\RelationInterface;


/**
 * @mixin EloquentModel
 */
trait RelationTrait
{
    protected function initializeRelationTrait()
    {
        if (! isset(static::$cacheRelationClasses[ static::class ])) {
            static::$cacheRelationClasses[ static::class ] = [];

            foreach ( $this->relationClasses() as $key => $class ) {
                try {
                    $rm = new \ReflectionMethod(static::class, $key);
                }
                catch ( \ReflectionException $e ) {
                    throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
                }

                if (count($rm->getParameters())) {
                    throw new RuntimeException(
                        [
                            'Relation method should not require any arguments',
                            $key,
                        ]
                    );
                }

                static::$cacheRelationClasses[ static::class ][ $key ] = $class;
            }
        }
    }


    /**
     * @var array<class-string<EloquentModel>, array<string, class-string<RelationInterface>>>
     */
    protected static $cacheRelationClasses = [];

    /**
     * @return array<string, class-string<RelationInterface>>
     */
    protected function relationClasses() : array
    {
        return [];
    }

    public function getRelationClass($key) : ?string
    {
        return static::$cacheRelationClasses[ static::class ][ $key ];
    }


    public function getRelationValue($key)
    {
        /** @see HasAttributes::getRelationValue() */

        return $this->doGetRelationValue($key);
    }

    protected function doGetRelationValue(string $key)
    {
        // > gzhegow, если имя свойства не является связью - то бросаем исключение
        if (! $this->isRelation($key)) {
            throw new LogicException(
                'Missing relation: ' . $key
            );
        }

        // > gzhegow, значение связи ранее было загружено - возвращаем его и на этом всё
        if ($this->relationLoaded($key)) {
            if ($result = $this->relations[ $key ]) {
                return $result;
            }
        }

        // > gzhegow, модель только что создана и еще не сохранена в БД
        if (! $this->exists) {
            $result = $this->doGetRelationValueDefault($key);

            return $result;
        }

        // > gzhegow, если флаг в модели запрещает делать под капотом запрос
        if (true === $this->preventsLazyLoading) {
            throw new RuntimeException(
                [
                    'Unable to ' . __METHOD__ . '.'
                    . ' You have to use `$model->load[Missing]()` / `$collection->load[Missing]()` / `$query->with()`'
                    . ' because flag `preventsLazyLoading` is set to TRUE',
                    //
                    $key,
                ]
            );

        } elseif (false === $this->preventsLazyLoading) {
            // > gzhegow, если флаг в модели разрешает делать ленивый запрос

            $result = null
                // > gzhegow, делаем запрос в БД, чтобы получить данные по связи
                ?? $this->getRelationshipFromMethod($key)
                ?? $this->doGetRelationValueDefault($key);

            return $result;

        } else {
            // > gzhegow, если флаг в модели не предполагает запрос

            $result = null
                // > gzhegow, не делаем запрос в БД
                ?? $this->doGetRelationValueDefault($key);

            return $result;
        }
    }

    protected function doGetRelationValueDefault(string $key) : ?EloquentCollection
    {
        if ($relation = $this->hasRelationshipMany($key)) {
            // > gzhegow, создаем пустую коллекцию

            $model = $relation->newModelInstance();

            $collection = $model->newCollection();

            $this->setRelation($key, $collection);

            $default = $collection;

        } else {
            // } elseif ($relation = $this->hasRelationshipOne($key)) {

            // > gzhegow, возвращаем NULL в качестве значения по-умолчанию

            $default = null;
        }

        return $default;
    }


    /**
     * @param string $key
     *
     * @return bool
     */
    public function isRelation($key)
    {
        /** @see HasAttributes::isRelation() */

        if (null === $this->hasRelation($key)) {
            return false;
        }

        return true;
    }


    /**
     * @template-covariant T of RelationInterface
     *
     * @param class-string<T>|null $relationClass
     *
     * @return class-string<T>|null
     */
    public function hasRelation(string $key, string $relationClass = null) : ?string
    {
        if ('' === $key) {
            return false;
        }

        $resultRelationClass = null;

        if (null === $resultRelationClass) {
            $isEqualsPivot = ($key === 'pivot');

            if ($isEqualsPivot) {
                if (isset($this->relations[ 'pivot' ])) {
                    $resultRelationClass = get_class($this->relations[ 'pivot' ]);
                }
            }
        }

        if (null === $resultRelationClass) {
            $modelClass = static::class;

            $existsInRelationClassCache = isset(static::$cacheRelationClasses[ $modelClass ][ $key ]);
            if (! $existsInRelationClassCache) {
                return null;
            }

            $resultRelationClass = static::$cacheRelationClasses[ $modelClass ][ $key ];
        }

        if (null !== $relationClass) {
            if (! is_a($resultRelationClass, $relationClass, true)) {
                return null;
            }
        }

        return $resultRelationClass;
    }

    /**
     * @return class-string<RelationInterface>|null
     */
    public function hasRelationOne(string $key) : ?string
    {
        $relationClass = $this->hasRelation($key);

        if (null === $relationClass) {
            return null;
        }

        if ((false
            || is_a($relationClass, BelongsTo::class, true)
            || is_a($relationClass, HasOne::class, true)
            || is_a($relationClass, MorphOne::class, true)
        )) {
            return $relationClass;
        }

        return null;
    }

    /**
     * @return class-string<RelationInterface>|null
     */
    public function hasRelationMany(string $key) : ?string
    {
        $relationClass = $this->hasRelation($key);

        if (null === $relationClass) {
            return null;
        }

        if (! (false
            || is_a($relationClass, BelongsTo::class, true)
            || is_a($relationClass, HasOne::class, true)
            || is_a($relationClass, MorphOne::class, true)
        )) {
            return $relationClass;
        }

        return null;
    }


    public function hasRelationship(string $key, ...$args) : ?RelationInterface
    {
        if (! $this->isRelation($key)) {
            return null;
        }

        $relationship = $this->{$key}(...$args);

        return $relationship;
    }

    public function hasRelationshipOne(string $key, ...$args) : ?RelationInterface
    {
        if (! $this->isRelation($key)) {
            return null;
        }

        $relationship = $this->{$key}(...$args);

        if ((
            $relationship instanceof BelongsTo
            || $relationship instanceof HasOne
            || $relationship instanceof MorphOne
        )) {
            return $relationship;
        }

        return null;
    }

    public function hasRelationshipMany(string $key, ...$args) : ?RelationInterface
    {
        if (! $this->isRelation($key)) {
            return null;
        }

        $relationship = $this->{$key}(...$args);

        if ((
            $relationship instanceof BelongsTo
            || $relationship instanceof HasOne
            || $relationship instanceof MorphOne
        )) {
            return null;
        }

        return $relationship;
    }


    /**
     * @return bool
     */
    public function relationLoaded($key)
    {
        /** @see HasRelationships::relationLoaded() */

        return $this->hasRelationLoaded($key);
    }

    /**
     * @template-covariant T of EloquentModel
     * @template-covariant TT of EloquentModelCollection<T>
     *
     * @param T|TT|null $result
     */
    public function hasRelationLoaded(string $relation, &$result = null) : bool
    {
        $result = null;

        $status = array_key_exists($relation, $this->relations);

        if ($status) {
            $result = $this->relations[ $relation ];
        }

        return $status;
    }

    /**
     * @template-covariant T of EloquentModel
     * @template-covariant TT of EloquentModelCollection
     *
     * @return T|TT
     */
    public function requireRelationLoaded(string $relation) // : object
    {
        $status = $this->hasRelationLoaded($relation, $result);

        if (! $status) {
            throw new RuntimeException(
                [
                    'The relation is required',
                    $relation,
                ]
            );
        }

        return $result;
    }


    /**
     * @return array{0: string, 1: string}
     */
    public function getMorphKeys(
        string $name,
        string $type = null,
        string $id = null
    ) : array
    {
        return [
            $type ?? $name . '_type',
            $id ?? $name . '_id',
        ];
    }

    /**
     * @deprecated
     * @internal
     */
    protected function getMorphs($name, $type, $id)
    {
        /** @see HasRelationships::getMorphs() */

        $morphs = $this->getMorphKeys($name, $type, $id);

        return $morphs;
    }
}
