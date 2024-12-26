<?php

namespace Gzhegow\Database\Package\Illuminate\Database;

use Gzhegow\Database\Exception\LogicException;
use Gzhegow\Database\Core\Query\PdoQuery\Traits\ChunkTrait;
use Illuminate\Support\Collection as EloquentSupportCollection;
use Gzhegow\Database\Core\Query\PdoQuery\Traits\PersistenceTrait;
use Illuminate\Database\Query\Builder as EloquentPdoQueryBuilderBase;
use Gzhegow\Database\Exception\Exception\Resource\ResourceNotFoundException;


class EloquentPdoQueryBuilder extends EloquentPdoQueryBuilderBase
{
    use ChunkTrait;
    use PersistenceTrait;


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
    public function firstOrFail($columns = [ '*' ])
    {
        $model = static::first($columns);

        if (null === $model) {
            throw new ResourceNotFoundException(
                [
                    'Resource not found',
                    $this,
                ]
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
}
