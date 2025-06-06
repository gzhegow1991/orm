<?php

namespace Gzhegow\Orm\Core\Query\PdoQuery\Traits;

use Gzhegow\Orm\Core\Orm;
use Gzhegow\Orm\Core\Persistence\EloquentPersistenceInterface;
use Gzhegow\Orm\Package\Illuminate\Database\EloquentPdoQueryBuilder;


/**
 * @mixin EloquentPdoQueryBuilder
 */
trait PersistenceTrait
{
    public function persistence() : EloquentPersistenceInterface
    {
        $persistence = Orm::persistence();

        return $persistence;
    }

    /**
     * @return static
     */
    public function persistQueryInsert(array $values)
    {
        $persistence = $this->persistence();

        $persistence->persistQueryForInsert($this, $values);

        return $this;
    }

    /**
     * @return static
     */
    public function persistQueryUpdate(array $values)
    {
        $persistence = $this->persistence();

        $persistence->persistQueryForUpdate($this, $values);

        return $this;
    }

    /**
     * @return static
     */
    public function persistQueryDelete()
    {
        $persistence = $this->persistence();

        $persistence->persistQueryForDelete($this);

        return $this;
    }
}
