<?php

namespace Gzhegow\Orm\Core\Query\PdoQuery\Traits;

use Gzhegow\Orm\Core\Orm;
use Gzhegow\Orm\Package\Illuminate\Database\EloquentPdoQueryBuilder;


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
