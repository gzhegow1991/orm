<?php

namespace Gzhegow\Orm\Package\Illuminate\Database\Eloquent;

use Gzhegow\Lib\Lib;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Gzhegow\Orm\Exception\LogicException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Gzhegow\Orm\Core\Query\ModelQuery\Traits\ChunkTrait;
use Illuminate\Support\Collection as EloquentSupportCollection;
use Gzhegow\Orm\Core\Query\ModelQuery\Traits\ColumnsTrait;
use Illuminate\Database\Query\Builder as EloquentPdoQueryBuilder;
use Gzhegow\Orm\Core\Query\ModelQuery\Traits\PersistenceTrait;
use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilderBase;
use Gzhegow\Orm\Exception\Exception\Resource\ResourceNotFoundException;


/**
 * @template-covariant T of EloquentModel
 */
class EloquentModelQueryBuilder extends EloquentQueryBuilderBase
{
    use ChunkTrait;
    use ColumnsTrait;
    use PersistenceTrait;


    /**
     * @var T
     */
    protected $model;


    /**
     * @param EloquentPdoQueryBuilder $query
     * @param T                       $model
     */
    public function __construct(
        EloquentPdoQueryBuilder $query,
        //
        EloquentModel $model
    )
    {
        parent::__construct($query);

        $this->setModel($model);
    }


    /**
     * @return EloquentPdoQueryBuilder
     */
    public function getQuery()
    {
        $pdoQuery = parent::getQuery();

        return $pdoQuery;
    }


    /**
     * @return T
     */
    public function getModel()
    {
        $model = parent::getModel();

        return $model;
    }

    /**
     * @param T $model
     *
     * @return static
     */
    public function setModel(Model $model)
    {
        $this->doSetModel($model);

        return $this;
    }

    private function doSetModel(EloquentModel $model)
    {
        parent::setModel($model);

        return $this;
    }


    /**
     * @return T
     */
    public function newModelInstance($attributes = [])
    {
        /** @see parent::newModelInstance() */

        $connection = $this->query->getConnection();
        $connectionName = $connection->getName();

        $instance = $this->model->newInstanceWithState($attributes);

        $instance->setConnection($connectionName);

        return $instance;
    }


    /**
     * @var static
     */
    protected $wheresGroupStack = [];

    /**
     * @return static
     */
    public function wheresGroup() // : static
    {
        $this->wheresGroupStack[] = $this->wheres;

        $this->wheres = [];

        return $this;
    }

    /**
     * @return static
     */
    public function endWheresGroup() // : static
    {
        if (! count($this->wheresGroupStack)) {
            throw new LogicException(
                'The `whereGroupWhereStack` is empty'
            );
        }

        $wheresLast = array_pop($this->wheresGroupStack);

        static::groupWheres($this);

        $queryPdo = $this->getQuery();

        $queryPdo->wheres = array_merge(
            $wheresLast,
            $queryPdo->wheres
        );

        return $this;
    }

    public static function groupWheres(EloquentModelQueryBuilder $query) : EloquentModelQueryBuilder
    {
        $queryPdo = $query->getQuery();

        $wheresCurrent = $queryPdo->wheres;

        $queryPdo->wheres = [];
        $queryPdo->where(
            static function (EloquentPdoQueryBuilder $queryPdoWhere) use ($wheresCurrent) {
                $queryPdoWhere->wheres = $wheresCurrent;
            }
        );

        return $query;
    }


    /**
     * @return EloquentModelCollection<T>|T[]
     *
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public function get($columnsDefault = null)
    {
        /** @see parent::get() */

        $columnsDefault = $columnsDefault ?? [];
        $columnsDefault = (array) $columnsDefault;

        $queryClone = clone $this;
        $queryClone = $this->applyScopesOnQuery($queryClone);

        $pdoQueryClone = $queryClone->getQuery();

        $model = $this->getModel();
        $_columnsDefault = $columnsDefault ?: $model->columnsDefault();
        $_columnsDefault = array_merge($_columnsDefault, $this->columnsDefaultAppend);
        $_columnsDefault = $model->prepareColumns($_columnsDefault);

        $rowsCollection = $pdoQueryClone->get($_columnsDefault);

        $models = $this->hydrateArray($rowsCollection->all());

        if (count($models)) {
            $models = $queryClone->eagerLoadRelations($models);
        }

        $collection = $model->newCollection($models);

        return $collection;
    }

    /**
     * @return EloquentSupportCollection<\stdClass>|\stdClass[]
     */
    public function getStd($columnsDefault = null)
    {
        $columnsDefault = $columnsDefault ?? [];
        $columnsDefault = (array) $columnsDefault;

        $queryClone = clone $this;
        $queryClone = $this->applyScopesOnQuery($queryClone);

        $pdoQueryClone = $queryClone->getQuery();

        $model = $this->getModel();
        $_columnsDefault = $columnsDefault ?: $model->columnsDefault();
        $_columnsDefault = array_merge($_columnsDefault, $this->columnsDefaultAppend);
        $_columnsDefault = $model->prepareColumns($_columnsDefault);

        $collection = $pdoQueryClone->get($_columnsDefault);

        return $collection;
    }

    /**
     * @return EloquentSupportCollection<int|string>|(int|string)[]
     */
    public function getKeys()
    {
        $model = $this->getModel();
        $modelKey = $model->getKeyName();

        $collection = $this->getStd([ $modelKey ]);

        $collection = $collection->keys();

        return $collection;
    }


    /**
     * @return T|null
     *
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public function first($columnsDefault = null)
    {
        /** @see parent::first() */

        $this->take(1);

        $collection = $this->get($columnsDefault);

        $model = $collection->first();

        return $model;
    }

    /**
     * @return T
     * @throws ResourceNotFoundException
     *
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public function firstOrFail($columnsDefault = null)
    {
        $model = $this->first($columnsDefault);

        if (null === $model) {
            throw new ResourceNotFoundException(
                [
                    'Resource not found',
                    get_class($this->model),
                    $this,
                ]
            );
        }

        return $model;
    }


    /**
     * @return \stdClass|null
     */
    public function firstStd($columnsDefault = null)
    {
        $this->take(1);

        $pdoQuery = $this->getQuery();

        $collection = $pdoQuery->get($columnsDefault);

        $row = $collection->first();

        return $row;
    }

    /**
     * @return \stdClass
     * @throws ResourceNotFoundException
     */
    public function firstStdOrFail($columnsDefault = null)
    {
        $model = $this->firstStd($columnsDefault);

        if (null === $model) {
            throw new ResourceNotFoundException(
                [
                    'Resource not found',
                    get_class($this->model),
                    $this,
                ]
            );
        }

        return $model;
    }


    /**
     * @return T|null
     */
    public function firstKey()
    {
        $model = $this->getModel();
        $modelKey = $model->getKeyName();

        $collection = $this->getStd([ $modelKey ]);

        $key = $collection->first();

        return $key;
    }

    /**
     * @return T
     * @throws ResourceNotFoundException
     */
    public function firstKeyOrFail()
    {
        $key = $this->firstKey();

        if (null === $key) {
            throw new ResourceNotFoundException(
                [
                    'Resource not found',
                    get_class($this->model),
                    $this,
                ]
            );
        }

        return $key;
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
                    get_class($this->model),
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
        $count = parent::count($columns);

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
     *
     * @internal
     * @deprecated
     */
    public function applyScopes()
    {
        /** @see parent::applyScopes() */

        $query = clone $this;

        $query = $this->applyScopesOnQuery($query);

        return $query;
    }

    public function applyScopesOnQuery(EloquentModelQueryBuilder $query)
    {
        if (! $this->scopes) {
            return $query;
        }

        foreach ( $this->scopes as $identifier => $scope ) {
            if (! isset($query->scopes[ $identifier ])) {
                continue;
            }

            $query->callScope(function ($query) use ($scope) {
                // If the scope is a Closure we will just go ahead and call the scope with the
                // builder instance. The "callScope" method will properly group the clauses
                // that are added to this query so "where" clauses maintain proper logic.
                if ($scope instanceof \Closure) {
                    $scope($query);
                }

                // If the scope is a scope object, we will call the apply method on this scope
                // passing in the builder and the model instance. After we run all of these
                // scopes we will return back the builder instance to the outside caller.
                if ($scope instanceof Scope) {
                    $scope->apply($query, $this->getModel());
                }
            });
        }

        return $query;
    }


    /**
     * @return EloquentModelCollection<T>|T[]
     *
     * @deprecated
     * @internal
     */
    public function hydrate(array $items)
    {
        /** @see parent::hydrate() */

        $result = $this->hydrateArray($items);

        $model = $this->getModel();
        $collection = $model->newCollection($result);

        return $collection;
    }

    /**
     * @return T[]
     *
     * @noinspection PhpDeprecationInspection
     */
    public function hydrateArray(array $items)
    {
        $model = static::getModel();

        $result = [];
        foreach ( $items as $i => $item ) {
            $attributes = Lib::php()->toArray($item);

            $result[ $i ] = $model->newFromBuilder($attributes);
        }

        return $result;
    }


    protected function parseWithRelations(array $relations)
    {
        /** @see parent::parseWithRelations() */

        $_relations = $this->parseWithRelations_prepareConstraints($relations);
        $_relations = $this->parseWithRelations_prepareConstraintScopes($_relations);

        return $_relations;
    }

    protected function parseWithRelations_prepareConstraints(array $relations) : array
    {
        $prepared = [];

        foreach ( $relations as $relationConfig => $relationClosure ) {
            if (is_int($relationConfig)) {
                $relationConfig = $relationClosure;
                $relationClosure = null;
            }

            $relationPath = explode('.', $relationConfig);

            $relationNameCurrent = [];
            $relationNameCurrentImplode = '';
            foreach ( $relationPath as $chunk ) {
                $explode = explode(':', $chunk);
                $relationName = $explode[ 0 ];
                $relationColumns = $explode[ 1 ] ?? '';

                $explode = strlen($relationColumns)
                    ? explode(',', $relationColumns)
                    : [];
                $relationColumns = $explode;

                $relationNameCurrent[] = $relationName;
                $relationNameCurrentImplode = implode('.', $relationNameCurrent);

                $prepared[ $relationNameCurrentImplode ] = [ $relationColumns, null ];
            }

            $relationNameLastImplode = $relationNameCurrentImplode;
            $prepared[ $relationNameLastImplode ][ 1 ] = $relationClosure;
        }

        return $prepared;
    }

    protected function parseWithRelations_prepareConstraintScopes(array $constraints) : array
    {
        foreach ( $constraints as $relationDot => $constraint ) {
            [ $constraintColumns, $constraintFn ] = $constraint;

            $constraintFn = function ($query) use (
                $constraintColumns,
                $constraintFn
            ) {
                $this->parseWithRelations_scopePrepareColumns(
                    $query,
                    $constraintColumns
                );

                if (null !== $constraintFn) {
                    $constraintFn($query);
                }
            };

            $constraints[ $relationDot ] = $constraintFn;
        }

        return $constraints;
    }

    protected function parseWithRelations_scopePrepareColumns(Relation $query, array $columnsUser = [])
    {
        /** @var EloquentPdoQueryBuilder $pdoQuery */
        /** @var EloquentModel $relatedModel */

        $columns = [];

        $pdoQuery = $query->getBaseQuery();
        foreach ( $pdoQuery->wheres as $where ) {
            $columns[] = $where[ 'column' ];
        }

        $relatedModel = $query->getRelated();
        $_columnsUser = $columnsUser ?: $relatedModel->columnsDefault();
        foreach ( $_columnsUser as $column ) {
            $columns[] = $column;
        }

        $columns = $relatedModel->prepareColumns(
            $columns,
            true
        );

        $pdoQuery->select($columns);
    }
}
