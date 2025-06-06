<?php

namespace Gzhegow\Orm\Package\Illuminate\Database;

use Gzhegow\Orm\Exception\LogicException;
use Illuminate\Database\ConnectionInterface;
use Gzhegow\Orm\Exception\Runtime\DatabaseException;
use Gzhegow\Orm\Core\Query\PdoQuery\Traits\ChunksTrait;
use Gzhegow\Orm\Core\Query\PdoQuery\Traits\PersistenceTrait;
use Gzhegow\Orm\Core\Query\PdoQuery\Traits\TransactionTrait;
use Illuminate\Support\Collection as EloquentSupportCollection;
use Illuminate\Database\Query\Builder as EloquentPdoQueryBuilderBase;
use Gzhegow\Orm\Exception\Exception\Resource\ResourceNotFoundException;


class EloquentPdoQueryBuilder extends EloquentPdoQueryBuilderBase
{
    use ChunksTrait;
    use PersistenceTrait;
    use TransactionTrait;


    /**
     * @var static
     */
    protected $wheresGroupStack = [];

    public function wheresGroup() : EloquentPdoQueryBuilder
    {
        $this->wheresGroupStack[] = $this->wheres;

        $this->wheres = [];

        return $this;
    }

    public function endWheresGroup() : EloquentPdoQueryBuilder
    {
        if (! count($this->wheresGroupStack)) {
            throw new LogicException(
                'The `whereGroupWhereStack` is empty'
            );
        }

        $wheresLast = array_pop($this->wheresGroupStack);

        static::groupWheres($this);

        $this->wheres = array_merge(
            $wheresLast,
            $this->wheres
        );

        return $this;
    }

    public static function groupWheres(EloquentPdoQueryBuilder $queryPdo) : EloquentPdoQueryBuilder
    {
        $wheresCurrent = $queryPdo->wheres;

        $queryPdo->wheres = [];
        $queryPdo->where(
            static function (EloquentPdoQueryBuilder $queryPdoWhere) use ($wheresCurrent) {
                $queryPdoWhere->wheres = $wheresCurrent;
            }
        );

        return $queryPdo;
    }


    /**
     * @return EloquentSupportCollection<\stdClass>
     */
    public function get($columns = [ '*' ])
    {
        $_columns = $columns ?: [ '*' ];
        $_columns = (array) $_columns;

        $collection = parent::get($_columns);

        return $collection;
    }


    /**
     * @return \stdClass|null
     */
    public function first($columns = [ '*' ])
    {
        $model = parent::first($columns);

        return $model;
    }

    /**
     * @return \stdClass
     * @throws ResourceNotFoundException
     */
    public function firstOrFail($columns = [ '*' ], $message = null)
    {
        /**
         * @see parent::firstOrFail()
         */

        $model = static::first($columns);

        if (null === $model) {
            throw new ResourceNotFoundException(
                [ $message ?? 'Resource not found', $this ]
            );
        }

        return $model;
    }


    /**
     * @return bool
     */
    public function exists()
    {
        $status = parent::exists();

        return $status;
    }

    /**
     * @return bool
     * @throws ResourceNotFoundException
     */
    public function existsOrFail()
    {
        $status = static::exists();

        if (null === $status) {
            throw new ResourceNotFoundException(
                [
                    'Resource not found',
                    $this,
                ]
            );
        }

        return $status;
    }


    /**
     * @return int
     */
    public function count($columns = '*')
    {
        $_columns = $columns ?: '*';

        $count = parent::count($_columns);

        return $count;
    }

    public function countExplain() : ?int
    {
        $rows = $this->explain();

        $count = end($rows)->rows ?: null;

        if (null !== $count) {
            $count = (int) $count;
        }

        return $count;
    }


    /**
     * @return \stdClass[]
     */
    public function explain() : array
    {
        $conn = $this->getConnection();

        $sql = $this->toSql();
        $bindings = $this->getBindings();

        $explainSql = "EXPLAIN {$sql};";

        $rows = $conn->select($explainSql, $bindings);

        return $rows;
    }


    /**
     * @return static
     */
    public function lockShared()
    {
        $this->lock('FOR SHARE');

        return $this;
    }

    /**
     * @return static
     */
    public function lockForUpdate()
    {
        $this->lock('FOR UPDATE');

        return $this;
    }

    /**
     * @return static
     */
    public function lockSharedNowait()
    {
        $useWaitIfNotSupported = $useWaitIfNotSupported ?? true;

        if ($this->lockIsNowaitAvailable()) {
            $this->lock('FOR SHARE NOWAIT');

        } elseif ($useWaitIfNotSupported) {
            $this->lock('FOR SHARE');

        } else {
            throw new DatabaseException(
                [ 'Your DB driver or version is not support `NOWAIT` locking' ]
            );
        }

        return $this;
    }

    /**
     * @return static
     */
    public function lockForUpdateNowait(?bool $useWaitIfNotSupported = null)
    {
        $useWaitIfNotSupported = $useWaitIfNotSupported ?? true;

        if ($this->lockIsNowaitAvailable()) {
            $this->lock('FOR UPDATE NOWAIT');

        } elseif ($useWaitIfNotSupported) {
            $this->lock('FOR UPDATE');

        } else {
            throw new DatabaseException(
                [ 'Your DB driver or version is not support `NOWAIT` locking' ]
            );
        }

        return $this;
    }

    protected function lockIsNowaitAvailable() : bool
    {
        $conn = $this->getConnection();

        $fn = function () {
            /** @var ConnectionInterface $this */

            if (! isset($this->isNowaitAvailable)) {
                $driver = $this->getDriverName();

                if ('mysql' === $driver) {
                    $version = $this
                        ->getPdo()
                        ->getAttribute(\PDO::ATTR_SERVER_VERSION)
                    ;

                    $this->isNowaitAvailable = version_compare($version, '8.0.0', '>=');

                } elseif ('pgsql' === $driver) {
                    $version = $this
                        ->getPdo()
                        ->getAttribute(\PDO::ATTR_SERVER_VERSION)
                    ;

                    $this->isNowaitAvailable = version_compare($version, '8.1.0', '>=');

                } else {
                    $this->isNowaitAvailable = false;
                }
            }

            return $this->isNowaitAvailable;
        };

        return $fn->call($conn);
    }
}
