<?php

namespace Gzhegow\Orm\Core\Query\PdoQuery\Traits;

use Gzhegow\Orm\Exception\RuntimeException;
use Gzhegow\Orm\Package\Illuminate\Database\EloquentPdoQueryBuilder;


/**
 * @mixin EloquentPdoQueryBuilder
 */
trait TransactionTrait
{
    public function transaction(\Closure $fn, $attempts = 1)
    {
        $conn = $this->getConnection();

        try {
            $result = $conn->transaction($fn, $attempts);
        }
        catch ( \Throwable $e ) {
            throw new RuntimeException($e);
        }

        return $result;
    }

    /**
     * @return void
     */
    public function beginTransaction()
    {
        $conn = $this->getConnection();

        $conn->beginTransaction();
    }

    /**
     * @return void
     */
    public function commit()
    {
        $conn = $this->getConnection();

        $conn->commit();
    }

    /**
     * @return void
     */
    public function rollBack()
    {
        $conn = $this->getConnection();

        $conn->rollBack();
    }

    /**
     * @return int
     */
    public function transactionLevel()
    {
        $conn = $this->getConnection();

        return $conn->transactionLevel();
    }
}
