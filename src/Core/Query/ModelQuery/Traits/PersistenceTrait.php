<?php

namespace Gzhegow\Orm\Core\Query\ModelQuery\Traits;

use Gzhegow\Orm\Core\Orm;
use Gzhegow\Orm\Core\Persistence\EloquentPersistenceInterface;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelQueryBuilder;


/**
 * @mixin EloquentModelQueryBuilder
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
    public function persistEloquentInsert(array $values)
    {
        $persistence = $this->persistence();

        $persistence->persistEloquentQueryForInsert($this, $values);

        return $this;
    }

    /**
     * @return static
     */
    public function persistEloquentUpdate(array $values)
    {
        $persistence = $this->persistence();

        $persistence->persistEloquentQueryForUpdate($this, $values);

        return $this;
    }

    /**
     * @return static
     */
    public function persistEloquentDelete()
    {
        $persistence = $this->persistence();

        $persistence->persistEloquentQueryForDelete($this);

        return $this;
    }
}
