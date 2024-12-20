<?php

namespace Gzhegow\Database\Package\Illuminate\Database\Eloquent\Relations;

use Gzhegow\Database\Core\Orm;
use Gzhegow\Database\Core\Relation\Traits\HasRelationNameTrait;
use Illuminate\Database\Eloquent\Relations\HasMany as BaseHasMany;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;


class HasMany extends BaseHasMany implements
    RelationInterface
{
    use HasRelationNameTrait;


    /**
     * @return static
     */
    public function persistForSave(EloquentModel $model)
    {
        $persistence = Orm::getEloquentPersistence();

        $persistence->persistHasOneOrManyForSave($this, $model);

        return $this;
    }

    /**
     * @return static
     */
    public function persistForSaveMany($models)
    {
        $persistence = Orm::getEloquentPersistence();

        $persistence->persistHasOneOrManyForSaveMany($this, $models);

        return $this;
    }
}
