<?php

namespace Gzhegow\Orm\Core\Model\Traits\Relation;

use Gzhegow\Orm\Core\Orm;
use Gzhegow\Orm\Exception\RuntimeException;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Gzhegow\Orm\Core\Relation\Factory\EloquentRelationFactory;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;


trait RelationFactoryTrait
{
    protected function relation() : EloquentRelationFactory
    {
        $factory = Orm::newEloquentRelationFactory($this);

        return $factory;
    }

    /**
     * @param callable|array|null $relationFn
     *
     * @return string|callable
     */
    public static function relationDot(
        ?array $relationFn = null,
        ?string $fields = null
    )
    {
        $relationDot = Orm::relationDot(
            $relationFn,
            $fields
        );

        return $relationDot;
    }


    protected function newBelongsTo(
        EloquentQueryBuilder $query,
        EloquentModel $child,
        $foreignKey, $ownerKey, $relation
    )
    {
        /**
         * @see HasRelationships::newBelongsTo()
         * @see static::relation()
         */

        throw new RuntimeException('You have to use ->relation()->' . __FUNCTION__ . '() to create relations');
    }


    protected function newHasOne(
        EloquentQueryBuilder $query,
        EloquentModel $parent,
        $foreignKey, $localKey
    )
    {
        /**
         * @see HasRelationships::newHasOne()
         * @see static::relation()
         */

        throw new RuntimeException('You have to use ->relation()->' . __FUNCTION__ . '() to create relations');
    }

    protected function newHasMany(
        EloquentQueryBuilder $query,
        EloquentModel $parent,
        $foreignKey, $localKey
    )
    {
        /**
         * @see HasRelationships::newHasMany()
         * @see static::relation()
         */

        throw new RuntimeException('You have to use ->relation()->' . __FUNCTION__ . '() to create relations');
    }


    protected function newBelongsToMany(
        EloquentQueryBuilder $query,
        EloquentModel $parent,
        $table,
        $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey,
        $relationName = null
    )
    {
        /**
         * @see HasRelationships::newBelongsToMany()
         * @see static::relation()
         */

        throw new RuntimeException('You have to use ->relation()->' . __FUNCTION__ . '() to create relations');
    }


    protected function newHasOneThrough(
        EloquentQueryBuilder $query,
        EloquentModel $farParent, EloquentModel $throughParent,
        $firstKey, $secondKey, $localKey, $secondLocalKey
    )
    {
        /**
         * @see HasRelationships::newHasOneThrough()
         * @see static::relation()
         */

        throw new RuntimeException('You have to use ->relation()->' . __FUNCTION__ . '() to create relations');
    }

    protected function newHasManyThrough(
        EloquentQueryBuilder $query,
        EloquentModel $farParent, EloquentModel $throughParent,
        $firstKey, $secondKey, $localKey, $secondLocalKey
    )
    {
        /**
         * @see HasRelationships::newHasManyThrough()
         * @see static::relation()
         */

        throw new RuntimeException('You have to use ->relation()->' . __FUNCTION__ . '() to create relations');
    }


    protected function newMorphOne(
        EloquentQueryBuilder $query,
        EloquentModel $parent,
        $type, $id, $localKey
    )
    {
        /**
         * @see HasRelationships::newMorphOne()
         * @see static::relation()
         */

        throw new RuntimeException('You have to use ->relation()->' . __FUNCTION__ . '() to create relations');
    }

    protected function newMorphMany(
        EloquentQueryBuilder $query,
        EloquentModel $parent,
        $type, $id, $localKey
    )
    {
        /**
         * @see HasRelationships::newMorphMany()
         * @see static::relation()
         */

        throw new RuntimeException('You have to use ->relation()->' . __FUNCTION__ . '() to create relations');
    }


    protected function newMorphTo(
        EloquentQueryBuilder $query,
        EloquentModel $parent,
        $foreignKey, $ownerKey, $type, $relation
    )
    {
        /**
         * @see HasRelationships::newMorphTo()
         * @see static::relation()
         */

        throw new RuntimeException('You have to use ->relation()->' . __FUNCTION__ . '() to create relations');
    }


    protected function newMorphToMany(
        EloquentQueryBuilder $query,
        EloquentModel $parent,
        $name, $table,
        $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey,
        $relationName = null,
        $inverse = false
    )
    {
        /**
         * @see HasRelationships::newMorphToMany()
         * @see static::relation()
         */

        throw new RuntimeException('You have to use ->relation()->' . __FUNCTION__ . '() to create relations');
    }

    protected function newMorphedByMany(
        EloquentQueryBuilder $query,
        EloquentModel $parent,
        $name, $table,
        $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey,
        $relationName = null,
        $inverse = true
    )
    {
        /**
         * @see HasRelationships::newMorphedByMany()
         * @see static::relation()
         */

        throw new RuntimeException('You have to use ->relation()->' . __FUNCTION__ . '() to create relations');
    }



    public function belongsTo(
        $related,
        $foreignKey = null, $ownerKey = null,
        $relation = null
    )
    {
        /**
         * @see HasRelationships::belongsTo()
         * @see static::relation()
         */

        throw new RuntimeException('You have to use ->relation()->' . __FUNCTION__ . '() to create relations');
    }


    public function hasOne(
        $related,
        $foreignKey = null, $localKey = null
    )
    {
        /**
         * @see HasRelationships::hasOne()
         * @see static::relation()
         */

        throw new RuntimeException('You have to use ->relation()->' . __FUNCTION__ . '() to create relations');
    }

    public function hasMany(
        $related,
        $foreignKey = null, $localKey = null
    )
    {
        /**
         * @see HasRelationships::hasMany()
         * @see static::relation()
         */

        throw new RuntimeException('You have to use ->relation()->' . __FUNCTION__ . '() to create relations');
    }


    public function belongsToMany(
        $related, $table = null,
        $foreignPivotKey = null, $relatedPivotKey = null,
        $parentKey = null, $relatedKey = null,
        $relation = null
    )
    {
        /**
         * @see HasRelationships::belongsToMany()
         * @see static::relation()
         */

        throw new RuntimeException('You have to use ->relation()->' . __FUNCTION__ . '() to create relations');
    }


    public function hasOneThrough(
        $related, $through,
        $firstKey = null, $secondKey = null,
        $localKey = null, $secondLocalKey = null
    )
    {
        /**
         * @see HasRelationships::hasOneThrough()
         * @see static::relation()
         */

        throw new RuntimeException('You have to use ->relation()->' . __FUNCTION__ . '() to create relations');
    }

    public function hasManyThrough(
        $related, $through,
        $firstKey = null, $secondKey = null,
        $localKey = null, $secondLocalKey = null
    )
    {
        /**
         * @see HasRelationships::hasManyThrough()
         * @see static::relation()
         */

        throw new RuntimeException('You have to use ->relation()->' . __FUNCTION__ . '() to create relations');
    }


    public function morphOne(
        $related,
        $name, $type = null,
        $id = null, $localKey = null
    )
    {
        /**
         * @see HasRelationships::morphOne()
         * @see static::relation()
         */

        throw new RuntimeException('You have to use ->relation()->' . __FUNCTION__ . '() to create relations');
    }

    public function morphMany(
        $related,
        $name, $type = null,
        $id = null, $localKey = null
    )
    {
        /**
         * @see HasRelationships::morphMany()
         * @see static::relation()
         */

        throw new RuntimeException('You have to use ->relation()->' . __FUNCTION__ . '() to create relations');
    }


    public function morphTo(
        $name = null, $type = null,
        $id = null, $ownerKey = null
    )
    {
        /**
         * @see HasRelationships::morphTo()
         * @see static::relation()
         */

        throw new RuntimeException('You have to use ->relation()->' . __FUNCTION__ . '() to create relations');
    }


    public function morphToMany(
        $related, $name, $table = null,
        $foreignPivotKey = null, $relatedPivotKey = null,
        $parentKey = null, $relatedKey = null,
        $relation = null, $inverse = false
    )
    {
        /**
         * @see HasRelationships::morphToMany()
         * @see static::relation()
         */

        throw new RuntimeException('You have to use ->relation()->' . __FUNCTION__ . '() to create relations');
    }

    public function morphedByMany(
        $related, $name, $table = null,
        $foreignPivotKey = null, $relatedPivotKey = null,
        $parentKey = null, $relatedKey = null, $relation = null
    )
    {
        /**
         * @see HasRelationships::morphedByMany()
         * @see static::relation()
         */

        throw new RuntimeException('You have to use ->relation()->' . __FUNCTION__ . '() to create relations');
    }


    protected function guessBelongsToRelation()
    {
        /** > замена метода произведена, поскольку метод не имеет практической пользы, в целях повышения производительности */

        /** @see HasRelationships::guessBelongsToRelation */

        throw new RuntimeException('Method replacement made due to no-use-case and increase performance');
    }

    protected function guessBelongsToManyRelation()
    {
        /** > замена метода произведена, поскольку метод не имеет практической пользы, в целях повышения производительности */

        /** @see HasRelationships::guessBelongsToManyRelation */

        throw new RuntimeException('Method replacement made due to no-use-case and increase performance');
    }
}
