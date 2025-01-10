<?php

namespace Gzhegow\Database\Core\Query\PdoQuery\Traits;

use Gzhegow\Database\Core\Orm;
use Gzhegow\Database\Package\Illuminate\Database\EloquentPdoQueryBuilder;


/**
 * @mixin EloquentPdoQueryBuilder
 */
trait PersistenceTrait
{
    /**
     * @return static
     */
    public function persistQueryInsert(array $values)
    {
        $persistence = Orm::eloquentPersistence();

        $persistence->persistQueryForInsert($this, $values);

        return $this;
    }

    /**
     * @return static
     */
    public function persistQueryUpdate(array $values)
    {
        $persistence = Orm::eloquentPersistence();

        $persistence->persistQueryForUpdate($this, $values);

        return $this;
    }

    /**
     * @return static
     */
    public function persistQueryDelete()
    {
        $persistence = Orm::eloquentPersistence();

        $persistence->persistQueryForDelete($this);

        return $this;
    }
}
