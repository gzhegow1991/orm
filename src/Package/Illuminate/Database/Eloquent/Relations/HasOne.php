<?php

namespace Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Relations;

use Gzhegow\Orm\Core\Orm;
use Illuminate\Database\Eloquent\Relations\HasOne as HasOneBase;
use Gzhegow\Orm\Core\Relation\Traits\HasRelationNameTrait;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModel;


class HasOne extends HasOneBase implements
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
