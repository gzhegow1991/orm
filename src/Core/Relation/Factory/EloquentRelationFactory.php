<?php

namespace Gzhegow\Orm\Core\Relation\Factory;

use Gzhegow\Lib\Lib;
use Gzhegow\Orm\Exception\LogicException;
use Gzhegow\Orm\Core\Relation\Spec\HasOneSpec;
use Gzhegow\Orm\Core\Relation\Spec\HasManySpec;
use Gzhegow\Orm\Core\Relation\Spec\MorphToSpec;
use Gzhegow\Orm\Core\Relation\Spec\MorphOneSpec;
use Gzhegow\Orm\Core\Relation\Spec\BelongsToSpec;
use Gzhegow\Orm\Core\Relation\Spec\MorphManySpec;
use Gzhegow\Orm\Core\Relation\Spec\MorphToManySpec;
use Gzhegow\Orm\Core\Relation\Spec\BelongsToManySpec;
use Gzhegow\Orm\Core\Relation\Spec\HasOneThroughSpec;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Gzhegow\Orm\Core\Relation\Spec\HasManyThroughSpec;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModel;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Relations\HasOne;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Relations\HasMany;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Relations\MorphTo;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Relations\MorphOne;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Relations\BelongsTo;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Relations\MorphMany;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Relations\MorphToMany;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Relations\HasManyThrough;


class EloquentRelationFactory implements EloquentRelationFactoryInterface
{
    /**
     * @var EloquentModel
     */
    protected $model;


    public function __construct(EloquentModel $model)
    {
        $this->model = $model;
    }


    public function newBelongsTo(BelongsToSpec $spec) : BelongsTo
    {
        /** @see HasRelationships::belongsTo() */

        $relationship = new BelongsTo(
            $spec->remoteModelQuery, $spec->thisModel,
            $spec->thisTableRightKey, $spec->remoteTableLeftKey,
            $spec->relationName
        );

        $relationship->setRelationName($spec->relationName);

        return $relationship;
    }


    public function newHasOne(HasOneSpec $spec) : HasOne
    {
        /** @see HasRelationships::hasOne() */

        $relationship = new HasOne(
            $spec->remoteModelQuery, $spec->thisModel,
            $spec->remoteTableLeftKey, $spec->thisTableRightKey
        );

        $relationship->setRelationName($spec->relationName);

        return $relationship;
    }

    public function newHasMany(HasManySpec $spec) : HasMany
    {
        /** @see HasRelationships::hasMany() */

        $relationship = new HasMany(
            $spec->remoteModelQuery, $spec->thisModel,
            $spec->remoteTableLeftKey, $spec->thisTableRightKey
        );

        $relationship->setRelationName($spec->relationName);

        return $relationship;
    }


    public function newBelongsToMany(BelongsToManySpec $spec) : BelongsToMany
    {
        /** @see HasRelationships::belongsToMany() */

        $relationship = new BelongsToMany(
            $spec->remoteModelQuery, $spec->thisModel, $spec->pivotModelClass,
            $spec->pivotTableLeftKey, $spec->pivotTableRightKey, $spec->thisTableRightKey, $spec->remoteTableLeftKey,
            $spec->relationName
        );

        $relationship->setRelationName($spec->relationName);

        return $relationship;
    }


    public function newHasOneThrough(HasOneThroughSpec $spec) : HasOneThrough
    {
        /** @see HasRelationships::hasOneThrough() */

        $relationship = new HasOneThrough(
            $spec->remoteModelQuery, $spec->thisModel, $spec->throughModel,
            $spec->throughTableLeftKey, $spec->remoteTableLeftKey, $spec->thisTableRightKey, $spec->throughTableRightKey
        );

        $relationship->setRelationName($spec->relationName);

        return $relationship;
    }

    public function newHasManyThrough(HasManyThroughSpec $spec) : HasManyThrough
    {
        /** @see HasRelationships::hasManyThrough() */

        $relationship = new HasManyThrough(
            $spec->remoteModelQuery, $spec->thisModel, $spec->throughModel,
            $spec->throughTableLeftKey, $spec->remoteTableLeftKey, $spec->thisTableRightKey, $spec->throughTableRightKey
        );

        $relationship->setRelationName($spec->relationName);

        return $relationship;
    }


    public function newMorphOne(MorphOneSpec $spec) : MorphOne
    {
        /** @see HasRelationships::morphOne() */

        $relationship = new MorphOne(
            $spec->remoteModelQuery, $spec->thisModel,
            $spec->morphTypeKey, $spec->morphIdKey,
            $spec->thisTableRightKey
        );

        $relationship->setRelationName($spec->relationName);

        return $relationship;
    }

    public function newMorphMany(MorphManySpec $spec) : MorphMany
    {
        /** @see HasRelationships::morphMany() */

        $relationship = new MorphMany(
            $spec->remoteModelQuery, $spec->thisModel,
            $spec->morphTypeKey, $spec->morphIdKey,
            $spec->thisTableRightKey
        );

        $relationship->setRelationName($spec->relationName);

        return $relationship;
    }


    public function newMorphTo(MorphToSpec $spec) : MorphTo
    {
        /** @see HasRelationships::morphTo() */

        $relationship = new MorphTo(
            $spec->morphModelQuery, $spec->thisModel,
            $spec->morphIdKey, $spec->remoteTableLeftKey, $spec->morphTypeKey,
            $spec->relationName
        );

        $relationship->setRelationName($spec->relationName);

        return $relationship;
    }

    public function newMorphToMany(MorphToManySpec $spec) : MorphToMany
    {
        /** @see HasRelationships::morphToMany() */

        $relationship = new MorphToMany(
            $spec->remoteModelQuery, $spec->thisModel,
            $spec->morphTypeName, $spec->morphTable,
            $spec->pivotTableLeftKey, $spec->pivotTableRightKey, $spec->thisTableRightKey, $spec->remoteTableLeftKey,
            $spec->relationName,
            $spec->inverse
        );

        $relationship->setRelationName($spec->relationName);

        return $relationship;
    }


    public function belongsTo(
        string $relationName,
        string $remoteModelClassOrTableName,
        string $thisTableRightKey = null, string $remoteTableLeftKey = null
    ) : BelongsTo
    {
        /** @see HasRelationships::belongsTo() */

        $spec = new BelongsToSpec();
        $spec->relationName = $relationName;
        $spec->remoteModelClassOrTableName = $remoteModelClassOrTableName;
        $spec->thisTableRightKey = $thisTableRightKey;
        $spec->remoteTableLeftKey = $remoteTableLeftKey;

        $_remoteModelClassOrTableName = $this->assertModelClassOrTableName($spec->remoteModelClassOrTableName);

        $spec->remoteModelClassOrTableName = $_remoteModelClassOrTableName;

        $thisModel = $this->model;

        $spec->thisModel = $thisModel;

        $remoteModel = $this->newModelWithSameConnection($_remoteModelClassOrTableName, $thisModel);
        $remoteModelQuery = $remoteModel->newQuery();

        $spec->remoteModel = $remoteModel;
        $spec->remoteModelQuery = $remoteModelQuery;

        if (null === $spec->thisTableRightKey) {
            $remoteModelTable = $remoteModel->getTable();
            $remoteModelKey = $remoteModel->getKeyName();

            $spec->thisTableRightKey = "{$remoteModelTable}_{$remoteModelKey}";
        }

        if (null === $spec->remoteTableLeftKey) {
            $spec->remoteTableLeftKey = $remoteModel->getKeyName();
        }

        $relationship = $this->newBelongsTo($spec);

        return $relationship;
    }


    public function hasOne(
        string $relationName,
        string $remoteModelClassOrTableName,
        string $remoteTableLeftKey = null, string $thisTableRightKey = null
    ) : HasOne
    {
        /** @see HasRelationships::hasOne() */

        $spec = new HasOneSpec();
        $spec->relationName = $relationName;
        $spec->remoteModelClassOrTableName = $remoteModelClassOrTableName;
        $spec->remoteTableLeftKey = $remoteTableLeftKey;
        $spec->thisTableRightKey = $thisTableRightKey;

        $_remoteModelClassOrTableName = $this->assertModelClassOrTableName($spec->remoteModelClassOrTableName);

        $spec->remoteModelClassOrTableName = $_remoteModelClassOrTableName;

        $thisModel = $this->model;

        $spec->thisModel = $thisModel;

        $remoteModel = $this->newModelWithSameConnection($_remoteModelClassOrTableName, $thisModel);
        $remoteModelQuery = $remoteModel->newQuery();

        $spec->remoteModel = $remoteModel;
        $spec->remoteModelQuery = $remoteModelQuery;

        if (null === $spec->thisTableRightKey) {
            $thisModelKey = $thisModel->getKeyName();

            $spec->thisTableRightKey = $thisModelKey;
        }

        if (null === $spec->remoteTableLeftKey) {
            $remoteModelTable = $remoteModel->getTable();

            $thisModelTable = $thisModel->getTable();
            $thisModelKey = $thisModel->getKeyName();

            $spec->remoteTableLeftKey = "{$remoteModelTable}.{$thisModelTable}_{$thisModelKey}";
        }

        $relationship = $this->newHasOne($spec);

        return $relationship;
    }

    public function hasMany(
        string $relationName,
        string $remoteModelClassOrTableName,
        string $remoteTableLeftKey = null, string $thisTableRightKey = null
    ) : HasMany
    {
        /** @see HasRelationships::hasMany() */

        $spec = new HasManySpec();
        $spec->relationName = $relationName;
        $spec->remoteModelClassOrTableName = $remoteModelClassOrTableName;
        $spec->remoteTableLeftKey = $remoteTableLeftKey;
        $spec->thisTableRightKey = $thisTableRightKey;

        $_remoteModelClassOrTableName = $this->assertModelClassOrTableName($spec->remoteModelClassOrTableName);

        $spec->remoteModelClassOrTableName = $_remoteModelClassOrTableName;

        $thisModel = $this->model;

        $spec->thisModel = $thisModel;

        $remoteModel = $this->newModelWithSameConnection($_remoteModelClassOrTableName, $thisModel);
        $remoteModelQuery = $remoteModel->newQuery();

        $spec->remoteModel = $remoteModel;
        $spec->remoteModelQuery = $remoteModelQuery;

        if (null === $spec->thisTableRightKey) {
            $thisModelKey = $thisModel->getKeyName();

            $spec->thisTableRightKey = $thisModelKey;
        }

        if (null === $spec->remoteTableLeftKey) {
            $remoteModelTable = $remoteModel->getTable();

            $thisModelTable = $thisModel->getTable();
            $thisModelKey = $thisModel->getKeyName();

            $spec->remoteTableLeftKey = "{$remoteModelTable}.{$thisModelTable}_{$thisModelKey}";
        }

        $relationship = $this->newHasMany($spec);

        return $relationship;
    }


    public function belongsToMany(
        string $relationName,
        string $remoteModelClassOrTableName,
        string $pivotModelClass = null,
        string $pivotTableLeftKey = null, string $pivotTableRightKey = null,
        string $thisTableRightKey = null, string $remoteTableLeftKey = null
    ) : BelongsToMany
    {
        /** @see HasRelationships::belongsToMany() */

        $spec = new BelongsToManySpec();
        $spec->relationName = $relationName;
        $spec->remoteModelClassOrTableName = $remoteModelClassOrTableName;
        $spec->pivotModelClass = $pivotModelClass;
        $spec->thisTableRightKey = $thisTableRightKey;
        $spec->pivotTableLeftKey = $pivotTableLeftKey;
        $spec->pivotTableRightKey = $pivotTableRightKey;
        $spec->remoteTableLeftKey = $remoteTableLeftKey;

        $_remoteModelClassOrTableName = $this->assertModelClassOrTableName($remoteModelClassOrTableName);

        $spec->remoteModelClassOrTableName = $_remoteModelClassOrTableName;

        $_pivotClass = $this->assertModelClass($pivotModelClass);

        $spec->pivotModelClass = $_pivotClass;

        $thisModel = $this->model;

        $spec->thisModel = $thisModel;

        $remoteModel = $this->newModelWithSameConnection($_remoteModelClassOrTableName, $thisModel);
        $remoteModelQuery = $remoteModel->newQuery();

        $spec->remoteModel = $remoteModel;
        $spec->remoteModelQuery = $remoteModelQuery;

        if (null === $spec->thisTableRightKey) {
            $spec->thisTableRightKey = $thisModel->getKeyName();
        }

        if (null === $spec->pivotTableLeftKey) {
            $thisModelTable = $thisModel->getTable();
            $thisModelKey = $thisModel->getKeyName();

            $spec->pivotTableLeftKey = "{$thisModelTable}_{$thisModelKey}";
        }

        if (null === $spec->pivotTableLeftKey) {
            $remoteModelTable = $remoteModel->getTable();
            $remoteModel = $remoteModel->getKeyName();

            $spec->pivotTableLeftKey = "{$remoteModelTable}_{$remoteModel}";
        }

        if (null === $spec->remoteTableLeftKey) {
            $spec->remoteTableLeftKey = $remoteModel->getKeyName();
        }

        $relationship = $this->newBelongsToMany($spec);

        return $relationship;
    }


    public function hasOneThrough(
        string $relationName,
        string $remoteModelClassOrTableName, string $throughModelClass,
        string $throughTableLeftKey = null, string $remoteTableLeftKey = null,
        string $thisTableRightKey = null, string $throughTableRightKey = null
    ) : HasOneThrough
    {
        /** @see HasRelationships::hasOneThrough() */

        $spec = new HasOneThroughSpec();
        $spec->relationName = $relationName;
        $spec->remoteModelClassOrTableName = $remoteModelClassOrTableName;
        $spec->throughModelClass = $throughModelClass;
        $spec->thisTableRightKey = $thisTableRightKey;
        $spec->throughTableLeftKey = $throughTableLeftKey;
        $spec->throughTableRightKey = $throughTableRightKey;
        $spec->remoteTableLeftKey = $remoteTableLeftKey;

        $_remoteModelClassOrTableName = $this->assertModelClassOrTableName($remoteModelClassOrTableName);

        $spec->remoteModelClassOrTableName = $_remoteModelClassOrTableName;

        $_throughModelClass = $this->assertModelClass($throughModelClass);

        $spec->throughModelClass = $_throughModelClass;

        $thisModel = $this->model;

        $spec->thisModel = $thisModel;

        $remoteModel = $this->newModelWithSameConnection($_remoteModelClassOrTableName, $thisModel);
        $remoteModelQuery = $remoteModel->newQuery();

        $spec->remoteModel = $remoteModel;
        $spec->remoteModelQuery = $remoteModelQuery;

        $throughModel = new $throughModelClass();

        $spec->throughModel = $throughModel;

        if (null === $spec->thisTableRightKey) {
            $spec->thisTableRightKey = $thisModel->getKeyName();
        }

        if (null === $spec->throughTableLeftKey) {
            $thisModelTable = $thisModel->getTable();
            $thisModelKey = $thisModel->getKeyName();

            $spec->throughTableLeftKey = "{$thisModelTable}_{$thisModelKey}";
        }

        if (null === $spec->throughTableRightKey) {
            $spec->throughTableRightKey = $thisModel->getKeyName();
        }

        if (null === $spec->remoteTableLeftKey) {
            $throughModelTable = $throughModel->getTable();
            $throughModelKey = $throughModel->getKeyName();

            $spec->remoteTableLeftKey = "{$throughModelTable}_{$throughModelKey}";
        }

        $relationship = $this->newHasOneThrough($spec);

        return $relationship;
    }

    public function hasManyThrough(
        string $relationName,
        string $remoteModelClassOrTableName, string $throughModelClass,
        string $throughTableLeftKey = null, string $remoteTableLeftKey = null,
        string $thisTableRightKey = null, string $throughTableRightKey = null
    ) : HasManyThrough
    {
        /** @see HasRelationships::hasManyThrough() */

        $spec = new HasManyThroughSpec();
        $spec->relationName = $relationName;
        $spec->remoteModelClassOrTableName = $remoteModelClassOrTableName;
        $spec->throughModelClass = $throughModelClass;
        $spec->thisTableRightKey = $thisTableRightKey;
        $spec->throughTableLeftKey = $throughTableLeftKey;
        $spec->throughTableRightKey = $throughTableRightKey;
        $spec->remoteTableLeftKey = $remoteTableLeftKey;

        $_remoteModelClassOrTableName = $this->assertModelClassOrTableName($remoteModelClassOrTableName);

        $spec->remoteModelClassOrTableName = $_remoteModelClassOrTableName;

        $_throughModelClass = $this->assertModelClass($throughModelClass);

        $spec->throughModelClass = $_throughModelClass;

        $thisModel = $this->model;

        $spec->thisModel = $thisModel;

        $remoteModel = $this->newModelWithSameConnection($_remoteModelClassOrTableName, $thisModel);
        $remoteModelQuery = $remoteModel->newQuery();

        $spec->remoteModel = $remoteModel;
        $spec->remoteModelQuery = $remoteModelQuery;

        $throughModel = new $throughModelClass();

        $spec->throughModel = $throughModel;

        if (null === $spec->thisTableRightKey) {
            $spec->thisTableRightKey = $thisModel->getKeyName();
        }

        if (null === $spec->throughTableLeftKey) {
            $thisModelTable = $thisModel->getTable();
            $thisModelKey = $thisModel->getKeyName();

            $spec->throughTableLeftKey = "{$thisModelTable}_{$thisModelKey}";
        }

        if (null === $spec->throughTableRightKey) {
            $spec->throughTableRightKey = $thisModel->getKeyName();
        }

        if (null === $spec->remoteTableLeftKey) {
            $throughModelTable = $throughModel->getTable();
            $throughModelKey = $throughModel->getKeyName();

            $spec->remoteTableLeftKey = "{$throughModelTable}_{$throughModelKey}";
        }

        $relationship = $this->newHasManyThrough($spec);

        return $relationship;
    }


    public function morphOne(
        string $relationName,
        string $remoteModelClassOrTableName,
        string $morphType, string $morphTypeKey = null, string $morphIdKey = null,
        string $thisTableRightKey = null
    ) : MorphOne
    {
        /** @see HasRelationships::morphOne() */

        $spec = new MorphOneSpec();
        $spec->relationName = $relationName;
        $spec->remoteModelClassOrTableName = $remoteModelClassOrTableName;
        $spec->morphType = $morphType;
        $spec->morphTypeKey = $morphTypeKey;
        $spec->morphIdKey = $morphIdKey;
        $spec->thisTableRightKey = $thisTableRightKey;

        $_remoteModelClassOrTableName = $this->assertModelClassOrTableName($remoteModelClassOrTableName);

        $spec->remoteModelClassOrTableName = $_remoteModelClassOrTableName;

        $thisModel = $this->model;

        $spec->thisModel = $thisModel;

        $remoteModel = $this->newModelWithSameConnection($_remoteModelClassOrTableName, $thisModel);
        $remoteModelQuery = $remoteModel->newQuery();

        $spec->remoteModel = $remoteModel;
        $spec->remoteModelQuery = $remoteModelQuery;

        [ $morphTypeKey, $morphIdKey ] = $thisModel->getMorphKeys(
            $morphType,    // taggable
            $morphTypeKey, // taggable_type
            $morphIdKey    // taggable_id
        );

        $remoteTable = $remoteModel->getTable();

        $morphTypeKey = "{$remoteTable}.{$morphTypeKey}";
        $morphIdKey = "{$remoteTable}.{$morphIdKey}";

        $spec->morphTypeKey = $morphTypeKey;
        $spec->morphIdKey = $morphIdKey;

        if (null === $spec->thisTableRightKey) {
            $spec->thisTableRightKey = $thisModel->getKeyName();
        }

        $relationship = $this->newMorphOne($spec);

        return $relationship;
    }

    public function morphMany(
        string $relationName,
        string $remoteModelClassOrTableName,
        string $morphType, string $morphTypeKey = null, string $morphIdKey = null,
        string $thisTableRightKey = null
    ) : MorphMany
    {
        /** @see HasRelationships::morphMany() */

        $spec = new MorphManySpec();
        $spec->relationName = $relationName;
        $spec->remoteModelClassOrTableName = $remoteModelClassOrTableName;
        $spec->morphType = $morphType;
        $spec->morphTypeKey = $morphTypeKey;
        $spec->morphIdKey = $morphIdKey;
        $spec->thisTableRightKey = $thisTableRightKey;

        $_remoteModelClassOrTableName = $this->assertModelClassOrTableName($remoteModelClassOrTableName);

        $spec->remoteModelClassOrTableName = $_remoteModelClassOrTableName;

        $thisModel = $this->model;

        $spec->thisModel = $thisModel;

        $remoteModel = $this->newModelWithSameConnection($_remoteModelClassOrTableName, $thisModel);
        $remoteModelQuery = $remoteModel->newQuery();

        $spec->remoteModel = $remoteModel;
        $spec->remoteModelQuery = $remoteModelQuery;

        [ $morphTypeKey, $morphIdKey ] = $thisModel->getMorphKeys(
            $morphType,    // taggable
            $morphTypeKey, // taggable_type
            $morphIdKey    // taggable_id
        );

        $remoteTable = $remoteModel->getTable();

        $morphTypeKey = "{$remoteTable}.{$morphTypeKey}";
        $morphIdKey = "{$remoteTable}.{$morphIdKey}";

        $spec->morphTypeKey = $morphTypeKey;
        $spec->morphIdKey = $morphIdKey;

        if (null === $spec->thisTableRightKey) {
            $spec->thisTableRightKey = $thisModel->getKeyName();
        }

        $relationship = $this->newMorphMany($spec);

        return $relationship;
    }


    public function morphTo(
        string $relationName,
        string $morphType = null, string $morphTypeKey = null, string $morphIdKey = null,
        string $remoteTableLeftKey = null
    ) : MorphTo
    {
        /** @see HasRelationships::morphTo() */

        $spec = new MorphToSpec();
        $spec->relationName = $relationName;
        $spec->morphType = $morphType;
        $spec->morphTypeKey = $morphTypeKey;
        $spec->morphIdKey = $morphIdKey;
        $spec->remoteTableLeftKey = $remoteTableLeftKey;

        $thisModel = $this->model;

        $spec->thisModel = $thisModel;

        [ $morphTypeKey, $morphIdKey ] = $thisModel->getMorphKeys(
            $morphType,     // taggable
            $morphTypeKey,  // taggable_type
            $morphIdKey     // taggable_id
        );

        $spec->morphTypeKey = $morphTypeKey;
        $spec->morphIdKey = $morphIdKey;

        if ($thisModel->hasRawAttribute($morphTypeKey, $result)) {
            $morphClass = $result;
            $morphModel = $this->newModelWithSameConnection($morphClass, $thisModel);
            $morphModelQuery = $morphModel->newQuery();

            if (null !== $spec->remoteTableLeftKey) {
                $spec->remoteTableLeftKey = $morphModel->getKeyName();
            }

        } else {
            $morphClass = null;
            $morphModel = null;
            $morphModelQuery = $thisModel->newQuery();

            $morphModelQuery->setEagerLoads([]);
        }

        $spec->morphClass = $morphClass;
        $spec->morphModel = $morphModel;
        $spec->morphModelQuery = $morphModelQuery;

        $relationship = $this->newMorphTo($spec);

        return $relationship;
    }


    public function morphToMany(
        string $relationName,
        string $remoteModelClassOrTableName, string $morphTypeName, string $morphTable = null,
        string $pivotTableLeftKey = null, string $pivotTableRightKey = null,
        string $thisTableRightKey = null, string $remoteTableLeftKey = null,
        bool $inverse = null
    ) : MorphToMany
    {
        /** @see HasRelationships::morphToMany() */

        $inverse = $inverse ?? false;

        $spec = new MorphToManySpec();
        $spec->relationName = $relationName;
        $spec->remoteModelClassOrTableName = $remoteModelClassOrTableName;
        $spec->morphTypeName = $morphTypeName;
        $spec->morphTable = $morphTable;
        $spec->thisTableRightKey = $thisTableRightKey;
        $spec->pivotTableLeftKey = $pivotTableLeftKey;
        $spec->pivotTableRightKey = $pivotTableRightKey;
        $spec->remoteTableLeftKey = $remoteTableLeftKey;
        $spec->inverse = $inverse;

        $_remoteModelClassOrTableName = $this->assertModelClassOrTableName($remoteModelClassOrTableName);

        $thisModel = $this->model;

        $spec->thisModel = $thisModel;

        $remoteModel = $this->newModelWithSameConnection($_remoteModelClassOrTableName, $thisModel);
        $remoteModelQuery = $remoteModel->newQuery();

        $spec->remoteModel = $remoteModel;
        $spec->remoteModelQuery = $remoteModelQuery;

        if (null === $spec->morphTable) {
            $spec->morphTable = $morphTypeName;
        }

        if (null === $spec->thisTableRightKey) {
            $spec->thisTableRightKey = $thisModel->getKeyName();
        }

        if (null === $spec->pivotTableLeftKey) {
            $spec->pivotTableLeftKey = "{$morphTypeName}_id";
        }

        if (null === $spec->pivotTableRightKey) {
            $remoteModelTable = $remoteModel->getTable();
            $remoteModelKey = $remoteModel->getKeyName();

            $spec->pivotTableRightKey = "{$remoteModelTable}_{$remoteModelKey}";
        }

        if (null === $spec->remoteTableLeftKey) {
            $spec->remoteTableLeftKey = $remoteModel->getKeyName();
        }

        $relationship = $this->newMorphToMany($spec);

        return $relationship;
    }

    public function morphedByMany(
        string $relationName,
        string $remoteModelClassOrTableName, string $morphTypeName, string $morphTable = null,
        string $pivotTableLeftKey = null, string $pivotTableRightKey = null,
        string $thisTableRightKey = null, string $remoteTableLeftKey = null,
        bool $inverse = null
    ) : MorphToMany
    {
        /** @see HasRelationships::morphedByMany() */

        $inverse = $inverse ?? true;

        $spec = new MorphToManySpec();
        $spec->relationName = $relationName;
        $spec->remoteModelClassOrTableName = $remoteModelClassOrTableName;
        $spec->morphTypeName = $morphTypeName;
        $spec->morphTable = $morphTable;
        $spec->thisTableRightKey = $thisTableRightKey;
        $spec->pivotTableLeftKey = $pivotTableLeftKey;
        $spec->pivotTableRightKey = $pivotTableRightKey;
        $spec->remoteTableLeftKey = $remoteTableLeftKey;
        $spec->inverse = $inverse;

        $_remoteModelClassOrTableName = $this->assertModelClassOrTableName($remoteModelClassOrTableName);

        $thisModel = $this->model;

        $spec->thisModel = $thisModel;

        $remoteModel = $this->newModelWithSameConnection($_remoteModelClassOrTableName, $thisModel);
        $remoteModelQuery = $remoteModel->newQuery();

        $spec->remoteModel = $remoteModel;
        $spec->remoteModelQuery = $remoteModelQuery;

        if (null === $spec->morphTable) {
            $spec->morphTable = $morphTypeName;
        }

        if (null === $spec->thisTableRightKey) {
            $spec->thisTableRightKey = $thisModel->getKeyName();
        }

        if (null === $spec->pivotTableLeftKey) {
            $remoteModelTable = $remoteModel->getTable();
            $remoteModelKey = $remoteModel->getKeyName();

            $spec->pivotTableLeftKey = "{$remoteModelTable}_{$remoteModelKey}";
        }

        if (null === $spec->pivotTableRightKey) {
            $spec->pivotTableRightKey = "{$morphTypeName}_id";
        }

        if (null === $spec->remoteTableLeftKey) {
            $spec->remoteTableLeftKey = $remoteModel->getKeyName();
        }

        $relationship = $this->newMorphToMany($spec);

        return $relationship;
    }


    protected function newModelWithSameConnection(string $modelClass, EloquentModel $modelSource) : EloquentModel
    {
        $instance = $modelSource->newModelWithState(
            $modelClass,
            [ 'connection' => $modelSource->getConnectionName() ]
        );

        return $instance;
    }


    /**
     * @param string $modelClass
     *
     * @return class-string<EloquentModel>
     */
    protected function assertModelClass(string $modelClass) : string
    {
        if (! is_subclass_of($modelClass, EloquentModel::class)) {
            throw new LogicException(
                [
                    'The `modelClass` should be class-string of: ' . EloquentModel::class,
                    $modelClass,
                ]
            );
        }

        return $modelClass;
    }

    /**
     * @param string $modelClassOrTableName
     *
     * @return class-string<EloquentModel>|string
     */
    protected function assertModelClassOrTableName(string $modelClassOrTableName) : string
    {
        if (false
            || ! Lib::parse()->struct_class($modelClassOrTableName)
            || is_subclass_of($modelClassOrTableName, EloquentModel::class)
        ) {
            return $modelClassOrTableName;
        }

        throw new LogicException(
            [
                'The `modelClassOrTableName` should be string (name of the table) or class-string of: ' . EloquentModel::class,
                $modelClassOrTableName,
            ]
        );
    }
}
