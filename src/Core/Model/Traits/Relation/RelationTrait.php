<?php

namespace Gzhegow\Orm\Core\Model\Traits\Relation;

use Gzhegow\Orm\Exception\LogicException;
use Gzhegow\Orm\Exception\RuntimeException;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModel;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelCollection;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Relations\RelationInterface;


/**
 * @mixin EloquentModel
 */
trait RelationTrait
{
    protected function initializeRelationTrait()
    {
        if (isset(static::$cacheRelationClasses[ static::class ])) {
            return;
        }

        static::$cacheRelationClasses[ static::class ] = [];

        foreach ( static::relationClasses() as $key => $class ) {
            if (! is_subclass_of($class, Relation::class)) {
                throw new RuntimeException(
                    [ 'The `class` should be class-string of: ' . Relation::class, $class, $key ]
                );
            }

            static::$cacheRelationClasses[ static::class ][ $key ] = $class;
        }
    }


    /**
     * @var array<class-string<EloquentModel>, array<string, class-string<RelationInterface>>>
     */
    protected static $cacheRelationClasses = [];

    /**
     * @return array<string, class-string<RelationInterface>>
     */
    abstract protected static function relationClasses() : array;

    public function getRelationClass($key) : ?string
    {
        return static::$cacheRelationClasses[ static::class ][ $key ];
    }


    public function getRelationValue($key)
    {
        /** @see HasAttributes::getRelationValue() */

        $value = $this->doGetRelationValue($key);

        return $value;
    }

    private function doGetRelationValue(string $key)
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

    private function doGetRelationValueDefault(string $key) : ?EloquentCollection
    {
        if ($relationship = $this->hasRelationshipMany($key)) {
            // > gzhegow, создаем пустую коллекцию

            $model = $relationship->newModelInstance();

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

        return false
            || $this->isRelationAttributeEloquent($key)
            || $this->isRelationAttributeApplication($key);
    }


    /**
     * @return class-string<RelationInterface>|null
     */
    public function hasRelation(string $key) : ?string
    {
        if (! $this->isRelationAttributeApplication($key)) {
            return null;
        }

        $relationClass = static::$cacheRelationClasses[ static::class ][ $key ];

        return $relationClass;
    }

    /**
     * @return class-string<BelongsTo|HasOne|MorphOne>|null
     */
    public function hasRelationOne(string $key) : ?string
    {
        if (null === ($relationClass = $this->hasRelation($key))) {
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
        if (null === ($relationClass = $this->hasRelation($key))) {
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

    /**
     * @template-covariant T of RelationInterface
     *
     * @param class-string<T> $ofClass
     *
     * @return class-string<T>|null
     */
    public function hasRelationOfClass(string $key, string $ofClass) : ?string
    {
        if (null === ($relationClass = $this->hasRelation($key))) {
            return false;
        }

        if (! is_a($relationClass, $ofClass, true)) {
            return null;
        }

        return $relationClass;
    }


    public function hasRelationship(string $key, ...$args) : ?RelationInterface
    {
        if (! $this->isRelationAttributeApplication($key)) {
            return null;
        }

        $relationship = $this->{$key}(...$args);

        return $relationship;
    }

    /**
     * @return BelongsTo|HasOne|MorphOne|null
     */
    public function hasRelationshipOne(string $key, ...$args) : ?RelationInterface
    {
        if (! $this->isRelationAttributeApplication($key)) {
            return null;
        }

        $relationship = $this->{$key}(...$args);

        if (false
            || $relationship instanceof BelongsTo
            || $relationship instanceof HasOne
            || $relationship instanceof MorphOne
        ) {
            return $relationship;
        }

        return null;
    }

    public function hasRelationshipMany(string $key, ...$args) : ?RelationInterface
    {
        if (! $this->isRelationAttributeApplication($key)) {
            return null;
        }

        $relationship = $this->{$key}(...$args);

        if (false
            || $relationship instanceof BelongsTo
            || $relationship instanceof HasOne
            || $relationship instanceof MorphOne
        ) {
            return null;
        }

        return $relationship;
    }

    /**
     * @template-covariant T of RelationInterface
     *
     * @param class-string<T> $ofClass
     *
     * @return T|null
     */
    public function hasRelationshipOfClass(string $key, string $ofClass, ...$args) : ?RelationInterface
    {
        if (! $this->isRelationAttributeApplication($key)) {
            return null;
        }

        $relationship = $this->{$key}(...$args);

        if (! ($relationship instanceof $ofClass)) {
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
                [ 'The relation should be loaded: ' . $relation, $relation ]
            );
        }

        return $result;
    }


    /**
     * @return array{ 0: string, 1: string }
     */
    public function getMorphKeys($name, $type = null, $id = null)
    {
        $morphs = [
            $type ?: $name . '_type',
            $id ?: $name . '_id',
        ];

        return $morphs;
    }

    /**
     * @return array{ 0: string, 1: string }
     *
     * @internal
     * @deprecated
     */
    protected function getMorphs($name, $type, $id)
    {
        /** @see HasRelationships::getMorphs() */

        $morphs = $this->getMorphKeys($name, $type, $id);

        return $morphs;
    }
}
