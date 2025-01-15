<?php

namespace Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Relations;

use Gzhegow\Orm\Core\Orm;
use Gzhegow\Orm\Core\Relation\Traits\HasRelationNameTrait;
use Illuminate\Database\Eloquent\Relations\HasMany as HasManyBase;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModel;


class HasMany extends HasManyBase implements
    RelationInterface
{
    use HasRelationNameTrait;


    /**
     * @return static
     */
    public function persistForSave(EloquentModel $model)
    {
        $persistence = Orm::eloquentPersistence();

        $persistence->persistHasOneOrManyForSave($this, $model);

        return $this;
    }

    /**
     * @return static
     */
    public function persistForSaveMany($models)
    {
        $persistence = Orm::eloquentPersistence();

        $persistence->persistHasOneOrManyForSaveMany($this, $models);

        return $this;
    }
}
