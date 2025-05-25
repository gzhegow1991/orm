<?php

namespace Gzhegow\Orm\Core\Model\Traits;

use Gzhegow\Orm\Core\Orm;
use Gzhegow\Orm\Core\Persistence\EloquentPersistenceInterface;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Base\EloquentModel;


/**
 * @mixin EloquentModel
 */
trait PersistenceTrait
{
    public function persistence() : EloquentPersistenceInterface
    {
        $persistence = Orm::eloquentPersistence();

        return $persistence;
    }

    /**
     * @return static
     */
    public function persistForSave()
    {
        $persistence = $this->persistence();

        $persistence->persistModelForSave($this);

        return $this;
    }

    /**
     * @return static
     */
    public function persistForDelete()
    {
        $persistence = $this->persistence();

        $persistence->persistModelForDelete($this);

        return $this;
    }


    /**
     * @return static
     */
    public function persistForSaveRecursive()
    {
        $persistence = $this->persistence();

        $persistence->persistModelForSaveRecursive($this);

        return $this;
    }

    /**
     * @return static
     */
    public function persistForDeleteRecursive()
    {
        $persistence = $this->persistence();

        $persistence->persistModelForDeleteRecursive($this);

        return $this;
    }
}
