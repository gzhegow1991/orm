<?php

namespace Gzhegow\Database\Package\Illuminate\Database\Eloquent;

use Gzhegow\Lib\Lib;
use Gzhegow\Database\Exception\RuntimeException;
use Gzhegow\Database\Core\Model\Traits\LoadTrait;
use Gzhegow\Database\Core\Model\Traits\TableTrait;
use Gzhegow\Database\Core\Model\Traits\QueryTrait;
use Gzhegow\Database\Core\Model\Traits\ChunkTrait;
use Gzhegow\Database\Core\Model\Traits\FactoryTrait;
use Gzhegow\Database\Core\Model\Traits\CalendarTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Gzhegow\Database\Core\Model\Traits\AttributeTrait;
use Gzhegow\Database\Core\Model\Traits\PersistenceTrait;
use Illuminate\Database\Eloquent\Model as EloquentModelBase;
use Gzhegow\Database\Core\Model\Traits\Relation\RelationTrait;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Gzhegow\Database\Core\Model\Traits\Relation\RelationFactoryTrait;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\Relations\MorphTo;
use Gzhegow\Database\Exception\Exception\Resource\ResourceNotFoundException;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\Relations\RelationInterface;


abstract class EloquentModel extends EloquentModelBase
{
    use RelationFactoryTrait;
    use RelationTrait;

    use AttributeTrait;
    use ChunkTrait;
    use FactoryTrait;
    use LoadTrait;
    use QueryTrait;
    use TableTrait;

    use CalendarTrait;
    use PersistenceTrait;


    // >>> metadata
    protected $table;
    protected $tablePrefix;
    protected $tableNoPrefix;
    protected $primaryKey = 'id';
    /** > список колонок БД, которые выбираются по-умолчанию: (NULL -> '*'; '' -> primaryKey, ['column' => true, 'column2' ] -> конкретные колонки) */
    protected $columns = null;

    // >>> settings
    public $incrementing = true;
    public $timestamps   = false;

    // >>> strict mode
    /** > ON __get(): `false|null` -> вернет null, `true` -> бросит исключение */
    public $preventsLazyGet = true;
    /** > ON __set(): `false|null` -> вернет null, `true` -> бросит исключение */
    public $preventsLazySet = true;
    /** > ON getRelationValue(): `null` -> вернет null|default, `false` -> выполнит SQL SELECT, `true` -> бросит исключение */
    public $preventsLazyLoading = null;

    // >>> attributes
    protected $attributes = [];
    protected $casts      = [];
    protected $dates      = [];
    /** > автоматическое преобразование ключей в `snake_case` при вызове ->toArray() */
    public static $snakeAttributes = false;

    // >>> relations
    protected $relations = [];
    /** > список связей модели для которых автоматически обновляются created_at/updated_at */
    protected $touches = [];

    // >>> serialization
    /** > список полей, которые принудительно скрываются при ->toArray(): ['column' => true, 'column2' ]) */
    protected $hidden       = [];
    protected $hiddenLoaded = false;
    /** > список полей, которые принудительно отображаются при ->toArray(): ['column' => true, 'column2' ]) */
    protected $visible       = [];
    protected $visibleLoaded = false;

    // >>> state
    /** > SELECT был сделан в рамках этого скрипта, чтобы создать сущность и наполнить её из БД */
    public $exists = false;
    /** > gzhegow, модель создана, чтобы выполнить INSERT в рамках текущего скрипта */
    public $recentlyCreated = false;
    /** > INSERT/UPDATE был сделан в рамках этого скрипта, т.е. модель создана "недавно", `exists` тоже будет true */
    public $wasRecentlyCreated = false;


    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    public function __get($key)
    {
        return $this->offsetGet($key);
    }

    public function __set($key, $value)
    {
        return $this->offsetSet($key, $value);
    }

    public function __unset($key)
    {
        return $this->offsetUnset($key);
    }


    public function offsetExists($offset)
    {
        if ($this->isRelationAttribute($offset)) {
            $exists = $this->isRelationAttributeExists($offset);

            return $exists;
        }

        if ($this->isModelAttribute($offset)) {
            $exists = $this->isModelAttributeExists($offset);

            return $exists;
        }

        return false;
    }

    public function offsetGet($offset)
    {
        if ($this->isRelationAttribute($offset)) {
            $value = $this->getRelationValue($offset);

            return $value;
        }

        if ($this->isModelAttribute($offset)) {
            if ($this->exists) {
                if (! $this->isModelAttributeExists($offset)) {
                    throw new RuntimeException(
                        'Missing attribute: ' . $offset
                    );
                }
            }

            $value = $this->getModelAttribute($offset);

            return $value;
        }

        return null;
    }

    public function offsetSet($offset, $value)
    {
        if ($this->isRelationAttribute($offset)) {
            $this->setRelationAttribute($offset, $value);
        }


        if ($this->isModelAttribute($offset)) {
            if ($this->preventsLazySet
                && ! ($this->exists || $this->recentlyCreated)
            ) {
                throw new RuntimeException(
                    'You have to create model using ::from() or get it by SELECT since you want to use __set(): '
                    . $offset
                );
            }

            if ($this->getKeyName() === $offset) {
                throw new RuntimeException(
                    [
                        'Primary key should be allocated using ->setupUuid() or auto(-increment) by remote storage: ' . $offset,
                        $offset,
                        $value,
                    ]
                );
            }

            $this->setModelAttribute($offset, $value);
        }

        return $this;
    }

    public function offsetUnset($offset)
    {
        if ($this->isRelationAttribute($offset)) {
            unset($this->relations[ $offset ]);
        }

        if ($this->isModelAttribute($offset)) {
            unset($this->attributes[ $offset ]);
        }

        return $this;
    }


    public function getKey()
    {
        /** @see parent::getKey(); */

        return $this->getAttribute(
            $this->getKeyName()
        );
    }

    public function getKeyName()
    {
        /** @see parent::getKeyName() */

        return $this->primaryKey;
    }


    public function getForeignKey()
    {
        /** @see parent::getForeignKey(); */

        $table = $this->getTable();
        $key = $this->getKeyName();

        return "{$table}_{$key}";
    }

    public function getForeignKeyName()
    {
        return $this->getForeignKey();
    }


    /**
     * @return static
     */
    public function fill(array $attributes)
    {
        /** @see parent::fill() */

        foreach ( $attributes as $attr => $value ) {
            if (! $this->isFillable($attr)) {
                throw new RuntimeException(
                    [
                        'Attribute is not fillable: ' . $attr,
                        $this,
                    ]
                );
            }

            $this->attributes[ $attr ] = $value;
        }

        return $this;
    }

    /**
     * @return static
     */
    public function fillPassed(array $attributes)
    {
        $_attributes = Lib::passed($attributes);

        $this->fill($_attributes);

        return $this;
    }


    public function save(array $options = null) : bool
    {
        $options = $options ?? [];

        $relationForeignKeys = [];
        foreach ( $this->relations as $relationName => $relationValue ) {
            if (! (false
                || $this->hasRelation($relationName, BelongsTo::class)
                || $this->hasRelation($relationName, MorphTo::class)
            )) {
                continue;
            }

            /** @var BelongsTo $relation */
            $relation = $this->{$relationName}();

            if (null === $relationValue) {
                $relation->dissociate();

            } else {
                $relation->associate($relationValue);

                $relationForeignKey = $relation->getForeignKeyName();

                $relationForeignKeys[ $relationForeignKey ] = $relationName;
            }
        }

        foreach ( $this->getAttributes() as $key => $value ) {
            if (isset($relationForeignKeys[ $key ]) && is_object($value)) {
                throw new RuntimeException(
                    'Unable to associate foreign key: '
                    . $relationForeignKeys[ $key ] . ' / ' . $key
                );
            }
        }

        $status = parent::save($options);

        return $status;
    }

    public function delete()
    {
        try {
            $status = parent::delete();
        }
        catch ( \Exception $e ) {
            throw new RuntimeException($e);
        }

        return $status;
    }


    public function saveRecursive() : bool
    {
        $graph = [];

        if (null === $this->doSaveRecursive($graph)) {
            return false;
        }

        return true;
    }

    protected function doSaveRecursive(array &$graph = null) : ?array
    {
        /** @var static $child */

        $graph = $graph ?? [];

        $splHash = spl_object_hash($this);

        if (isset($graph[ $splHash ])) {
            return $graph;
        }

        $graph[ $splHash ] = $this;

        // > gzhegow, this will remove cross-links for garbage collector
        $relationsBelongsTo = [];

        foreach ( $this->relations as $relationName => $relationValue ) {
            if (null === $relationValue) {
                continue;
            }

            if (! (false
                || $this->hasRelation($relationName, BelongsTo::class)
                || $this->hasRelation($relationName, MorphTo::class)
            )) {
                continue;
            }

            $relationsBelongsTo[ $relationName ] = true;

            /** @var EloquentModel $parent */
            $parent = $relationValue;

            // ! recursion
            if (null === $parent->doSaveRecursive($graph)) {
                return null;
            }
        }

        $status = $this->save();
        if (! $status) {
            return null;
        }

        foreach ( $this->relations as $relationName => $relationValue ) {
            if (null === $relationValue) {
                continue;
            }

            if (isset($relationsBelongsTo[ $relationName ])) {
                continue;
            }

            $children = is_a($relationValue, EloquentCollection::class)
                ? $relationValue->all()
                : ($relationValue ? [ $relationValue ] : []);

            foreach ( $children as $child ) {
                /** @var EloquentModel $model */

                // ! recursion
                if (null === $child->doSaveRecursive($graph)) {
                    return null;
                }
            }
        }

        // > gzhegow, remove cross-links for garbage collector
        foreach ( $relationsBelongsTo as $relationName => $bool ) {
            unset($this->relations[ $relationName ]);
        }

        return $graph;
    }


    public function deleteRecursive() : ?array
    {
        $graph = [];

        $graph = $this->doDeleteRecursive($graph);

        return $graph;
    }

    protected function doDeleteRecursive(array &$graph = null) : ?array
    {
        /** @var static $model */

        $graph = $graph ?? [];

        $splHash = spl_object_hash($this);

        if (isset($graph[ $splHash ])) {
            return $graph;
        }

        $graph[ $splHash ] = $this->relations;

        foreach ( $this->relations as $relationName => $relationValue ) {
            if (null === $relationValue) {
                continue;
            }

            if (false
                || $this->hasRelation($relationName, BelongsTo::class)
                || $this->hasRelation($relationName, MorphTo::class)
            ) {
                continue;
            }

            $models = is_a($relationValue, EloquentCollection::class)
                ? $relationValue->all()
                : ($relationValue ? [ $relationValue ] : []);

            foreach ( $models as $model ) {
                if (null === $model->doDeleteRecursive($graph)) {
                    return null;
                }
            }
        }

        if (! $this->delete()) {
            return null;
        }

        return $graph;
    }


    /**
     * @return EloquentModelCollection<static>|static[]
     */
    public static function get(EloquentModelQueryBuilder $query, $columns = [ '*' ])
    {
        return $query->get($columns);
    }

    /**
     * @return static|null
     */
    public static function first(EloquentModelQueryBuilder $query, $columns = [ '*' ])
    {
        return $query->first($columns);
    }

    /**
     * @return static
     * @throws ResourceNotFoundException
     */
    public static function firstOrFail(EloquentModelQueryBuilder $query, $columns = [ '*' ])
    {
        return $query->firstOrFail($columns);
    }


    /**
     * > gzhegow, немного измененный вывод объекта в json, чтобы свойства со связями не перемешивались
     */
    public function toArray()
    {
        $array = $this->attributesToArray();

        if ($relationsArray = $this->relationsToArray()) {
            ksort($relationsArray);

            $array[ '_relations' ] = $relationsArray;
        }

        return $array;
    }


    /**
     * @return array<string, class-string<RelationInterface>>
     */
    abstract protected function relationClasses() : array;


    /**
     * @return static
     */
    public static function getModel()
    {
        return static::$models[ $class = static::class ] = null
            ?? static::$models[ $class ]
            ?? new static();
    }

    /**
     * @var array
     */
    protected static $models = [];
}
