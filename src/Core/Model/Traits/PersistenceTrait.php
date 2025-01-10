<?php

namespace Gzhegow\Database\Core\Model\Traits;

use Gzhegow\Database\Core\Orm;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;


/**
 * @mixin EloquentModel
 */
trait PersistenceTrait
{
    /**
     * @return static
     */
    public function persistForSave()
    {
        $persistence = Orm::eloquentPersistence();

        $persistence->persistModelForSave($this);

        return $this;
    }

    /**
     * @return static
     */
    public function persistForDelete()
    {
        $persistence = Orm::eloquentPersistence();

        $persistence->persistModelForDelete($this);

        return $this;
    }


    /**
     * @return static
     */
    public function persistForSaveRecursive()
    {
        $persistence = Orm::eloquentPersistence();

        $persistence->persistModelForSaveRecursive($this);

        return $this;
    }

    /**
     * @return static
     */
    public function persistForDeleteRecursive()
    {
        $persistence = Orm::eloquentPersistence();

        $persistence->persistModelForDeleteRecursive($this);

        return $this;
    }
}
