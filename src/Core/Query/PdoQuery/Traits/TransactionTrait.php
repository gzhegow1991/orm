<?php

namespace Gzhegow\Orm\Core\Query\PdoQuery\Traits;

use Gzhegow\Orm\Exception\RuntimeException;
use Gzhegow\Orm\Package\Illuminate\Database\EloquentPdoQueryBuilder;


/**
 * @mixin EloquentPdoQueryBuilder
 */
trait TransactionTrait
{
    /**
     * @return mixed|false
     */
    public function transaction(\Closure $fn, $attempts = 1, array $refs = [])
    {
        $withThrowable = array_key_exists(0, $refs);
        if ($withThrowable) {
            $refThrowable =& $refs[ 0 ];
        }
        $refThrowable = null;

        $conn = $this->getConnection();

        try {
            $result = $conn->transaction($fn, $attempts);
        }
        catch ( \Throwable $e ) {
            if ($withThrowable) {
                $refThrowable = $e;

                return false;
            }

            throw new RuntimeException('Unhandled exception on ' . __FUNCTION__, $e);
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function beginTransaction(array $refs = [])
    {
        $withThrowable = array_key_exists(0, $refs);
        if ($withThrowable) {
            $refThrowable =& $refs[ 0 ];
        }
        $refThrowable = null;

        $conn = $this->getConnection();

        try {
            $conn->beginTransaction();
        }
        catch ( \Throwable $e ) {
            if ($withThrowable) {
                $refThrowable = $e;

                return false;
            }

            throw new RuntimeException('Unhandled exception on ' . __FUNCTION__, $e);
        }

        return true;
    }

    /**
     * @return bool
     */
    public function commit(array $refs = [])
    {
        $withThrowable = array_key_exists(0, $refs);
        if ($withThrowable) {
            $refThrowable =& $refs[ 0 ];
        }
        $refThrowable = null;

        $conn = $this->getConnection();

        try {
            $conn->commit();
        }
        catch ( \Throwable $e ) {
            if ($withThrowable) {
                $refThrowable = $e;

                return false;
            }

            throw new RuntimeException('Unhandled exception on ' . __FUNCTION__, $e);
        }

        return true;
    }

    /**
     * @return bool
     */
    public function rollBack(array $refs = [])
    {
        $withThrowable = array_key_exists(0, $refs);
        if ($withThrowable) {
            $refThrowable =& $refs[ 0 ];
        }
        $refThrowable = null;

        $conn = $this->getConnection();

        try {
            $conn->rollBack();
        }
        catch ( \Throwable $e ) {
            if ($withThrowable) {
                $refThrowable = $e;

                return false;
            }

            throw new RuntimeException('Unhandled exception on ' . __FUNCTION__, $e);
        }

        return true;
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
