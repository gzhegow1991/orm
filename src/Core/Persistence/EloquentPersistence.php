<?php

namespace Gzhegow\Orm\Core\Persistence;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Gzhegow\Orm\Exception\Runtime\DatabaseException;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Gzhegow\Orm\Package\Illuminate\Database\Capsule\Eloquent;
use Illuminate\Database\Query\Builder as EloquentPdoQueryBuilderBase;
use Illuminate\Database\Eloquent\Builder as EloquentModelQueryBuilderBase;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Base\EloquentModel;


class EloquentPersistence implements EloquentPersistenceInterface
{
    /**
     * @var Eloquent
     */
    protected $eloquent;

    /**
     * @var array<int, callable>
     */
    protected $queue = [];
    /**
     * @var array<int, array>
     */
    protected $queueArgs = [];

    /**
     * @var array<int, bool>
     */
    protected $queueBelongsToManySave = [];
    /**
     * @var array<int, bool>
     */
    protected $queueBelongsToManySaveMany = [];
    /**
     * @var array<int, bool>
     */
    protected $queueBelongsToManySync = [];

    /**
     * @var array<int, bool>
     */
    protected $queueHasOneOrManySave = [];
    /**
     * @var array<int, bool>
     */
    protected $queueHasOneOrManySaveMany = [];

    /**
     * @var array<int, bool>
     */
    protected $queueModelSaveRecursive = [];
    /**
     * @var array<int, bool>
     */
    protected $queueModelDeleteRecursive = [];

    /**
     * @var array<int, bool>
     */
    protected $queueModelSave = [];
    /**
     * @var array<int, bool>
     */
    protected $queueModelDelete = [];

    /**
     * @var array<int, bool>
     */
    protected $queueEloquentInsert = [];
    /**
     * @var array<int, bool>
     */
    protected $queueEloquentUpdate = [];
    /**
     * @var array<int, bool>
     */
    protected $queueEloquentDelete = [];

    /**
     * @var array<int, bool>
     */
    protected $queueQueryInsert = [];
    /**
     * @var array<int, bool>
     */
    protected $queueQueryUpdate = [];
    /**
     * @var array<int, bool>
     */
    protected $queueQueryDelete = [];

    /**
     * @var array<int, bool>
     */
    protected $queueSqlStatement = [];

    /**
     * @var array<string, int>
     */
    protected $mapBelongsToManySave = [];
    /**
     * @var array<string, int>
     */
    protected $mapBelongsToManySaveMany = [];
    /**
     * @var array<string, int>
     */
    protected $mapBelongsToManySync = [];

    /**
     * @var array<string, int>
     */
    protected $mapHasOneOrManySave = [];
    /**
     * @var array<string, int>
     */
    protected $mapHasOneOrManySaveMany = [];

    /**
     * @var array<string, int>
     */
    protected $mapModelSaveRecursive = [];
    /**
     * @var array<string, int>
     */
    protected $mapModelDeleteRecursive = [];

    /**
     * @var array<string, int>
     */
    protected $mapModelSave = [];
    /**
     * @var array<string, int>
     */
    protected $mapModelDelete = [];


    public function __construct(Eloquent $eloquent)
    {
        $this->eloquent = $eloquent;
    }


    public function reset() : void
    {
        $this->queue = [];
        $this->queueArgs = [];

        $this->queueModelSaveRecursive = [];
        $this->queueModelDeleteRecursive = [];

        $this->queueModelSave = [];
        $this->queueModelDelete = [];

        $this->mapModelSaveRecursive = [];
        $this->mapModelDeleteRecursive = [];

        $this->mapModelSave = [];
        $this->mapModelDelete = [];
    }


    public function persistBelongsToManyForSave(
        BelongsToMany $relation,
        EloquentModel $model, array $pivotAttributes = [], ?bool $touch = null
    ) : void
    {
        $touch = $touch ?? true;

        $idx = count($this->queue);

        $relationKey = $this->relationKey($relation);

        $this->queue[ $idx ] = [ $relation, 'save' ];
        $this->queueArgs[ $idx ] = [ $model, $pivotAttributes, $touch ];
        $this->queueBelongsToManySave[ $idx ] = true;

        $this->mapBelongsToManySave[ $relationKey ] = $idx;
    }

    public function persistBelongsToManyForSaveMany(
        BelongsToMany $relation,
        $models, array $pivotAttributes = []
    ) : void
    {
        $idx = count($this->queue);

        $relationKey = $this->relationKey($relation);

        $this->queue[ $idx ] = [ $relation, 'saveMany' ];
        $this->queueArgs[ $idx ] = [ $models, $pivotAttributes ];
        $this->queueBelongsToManySaveMany[ $idx ] = true;

        $this->mapBelongsToManySaveMany[ $relationKey ] = $idx;
    }

    public function persistBelongsToManyForSync(
        BelongsToMany $relation,
        $ids, ?bool $detaching = null
    ) : void
    {
        $detaching = $detaching ?? true;

        $idx = count($this->queue);

        $relationKey = $this->relationKey($relation);

        $this->queue[ $idx ] = [ $relation, 'sync' ];
        $this->queueArgs[ $idx ] = [ $ids, $detaching ];
        $this->queueBelongsToManySync[ $idx ] = true;

        $this->mapBelongsToManySync[ $relationKey ] = $idx;
    }


    public function persistHasOneOrManyForSave(
        HasOneOrMany $relation,
        EloquentModel $model
    ) : void
    {
        $idx = count($this->queue);

        $relationKey = $this->relationKey($relation);

        $this->queue[ $idx ] = [ $relation, 'save' ];
        $this->queueArgs[ $idx ] = [ $model ];
        $this->queueHasOneOrManySave[ $idx ] = true;

        $this->mapHasOneOrManySave[ $relationKey ] = $idx;
    }

    public function persistHasOneOrManyForSaveMany(
        HasOneOrMany $relation,
        $models
    ) : void
    {
        $idx = count($this->queue);

        $relationKey = $this->relationKey($relation);

        $this->queue[ $idx ] = [ $relation, 'saveMany' ];
        $this->queueArgs[ $idx ] = [ $models ];
        $this->queueHasOneOrManySaveMany[ $idx ] = true;

        $this->mapHasOneOrManySaveMany[ $relationKey ] = $idx;
    }


    public function persistModelForSaveRecursive(EloquentModel $model) : void
    {
        $idx = count($this->queue);

        $modelKey = $this->modelKey($model);

        if (isset($this->mapModelSaveRecursive[ $modelKey ])) {
            $idxOld = $this->mapModelSaveRecursive[ $modelKey ];

            unset($this->queue[ $idxOld ]);
            unset($this->queueArgs[ $idxOld ]);
            unset($this->queueModelSaveRecursive[ $idxOld ]);

            unset($this->mapModelSaveRecursive[ $modelKey ]);
        }

        if (isset($this->mapModelSave[ $modelKey ])) {
            $idxOld = $this->mapModelSave[ $modelKey ];

            unset($this->queue[ $idxOld ]);
            unset($this->queueArgs[ $idxOld ]);
            unset($this->queueModelSave[ $idxOld ]);

            unset($this->mapModelSave[ $modelKey ]);
        }

        $this->queue[ $idx ] = [ $model, 'saveRecursive' ];
        $this->queueArgs[ $idx ] = [];
        $this->queueModelSaveRecursive[ $idx ] = true;

        $this->mapModelSaveRecursive[ $modelKey ] = $idx;
    }

    public function persistModelForDeleteRecursive(EloquentModel $model) : void
    {
        $idx = count($this->queue);

        $modelKey = $this->modelKey($model);

        $idx = null
            ?? $this->mapModelDeleteRecursive[ $modelKey ]
            ?? $this->mapModelDelete[ $modelKey ]
            ?? $idx;

        unset($this->queueModelDelete[ $idx ]);
        unset($this->queueModelDeleteRecursive[ $idx ]);

        unset($this->mapModelDelete[ $modelKey ]);
        unset($this->mapModelDeleteRecursive[ $modelKey ]);

        $this->queue[ $idx ] = [ $model, 'deleteRecursive' ];
        $this->queueArgs[ $idx ] = [];
        $this->queueModelDeleteRecursive[ $idx ] = true;

        $this->mapModelDeleteRecursive[ $modelKey ] = $idx;
    }


    public function persistModelForSave(EloquentModel $model) : void
    {
        $idx = count($this->queue);

        $modelKey = $this->modelKey($model);

        $typeHighOrder = null
            ?? (isset($this->mapModelSaveRecursive[ $modelKey ]) ? 'saveRecursive' : null)
            ?? (isset($this->mapModelSave[ $modelKey ]) ? 'save' : null)
            ?? 'save';

        if (isset($this->mapModelSaveRecursive[ $modelKey ])) {
            $idxOld = $this->mapModelSaveRecursive[ $modelKey ];

            unset($this->queue[ $idxOld ]);
            unset($this->queueArgs[ $idxOld ]);
            unset($this->queueModelSaveRecursive[ $idxOld ]);

            unset($this->mapModelSaveRecursive[ $modelKey ]);
        }

        if (isset($this->mapModelSave[ $modelKey ])) {
            $idxOld = $this->mapModelSave[ $modelKey ];

            unset($this->queue[ $idxOld ]);
            unset($this->queueArgs[ $idxOld ]);
            unset($this->queueModelSave[ $idxOld ]);

            unset($this->mapModelSave[ $modelKey ]);
        }

        switch ( $typeHighOrder ):
            case 'saveRecursive':
                $this->queue[ $idx ] = [ $model, 'saveRecursive' ];
                $this->queueArgs[ $idx ] = [];
                $this->queueModelSaveRecursive[ $idx ] = true;

                $this->mapModelSaveRecursive[ $modelKey ] = $idx;

                break;

            case 'save':
                $this->queue[ $idx ] = [ $model, 'save' ];
                $this->queueArgs[ $idx ] = [];
                $this->queueModelSave[ $idx ] = true;

                $this->mapModelSave[ $modelKey ] = $idx;

                break;

        endswitch;
    }

    public function persistModelForDelete(EloquentModel $model) : void
    {
        $idx = count($this->queue);

        $modelKey = $this->modelKey($model);

        $typeHighOrder = null
            ?? (isset($this->mapModelDeleteRecursive[ $modelKey ]) ? 'deleteRecursive' : null)
            ?? (isset($this->mapModelDelete[ $modelKey ]) ? 'delete' : null)
            ?? 'delete';

        $idx = null
            ?? $this->mapModelDeleteRecursive[ $modelKey ]
            ?? $this->mapModelDelete[ $modelKey ]
            ?? $idx;

        unset($this->queueModelDelete[ $idx ]);
        unset($this->queueModelDeleteRecursive[ $idx ]);

        unset($this->mapModelDelete[ $modelKey ]);
        unset($this->mapModelDeleteRecursive[ $modelKey ]);

        switch ( $typeHighOrder ):
            case 'deleteRecursive':
                $this->queue[ $idx ] = [ $model, 'deleteRecursive' ];
                $this->queueArgs[ $idx ] = [];
                $this->queueModelDeleteRecursive[ $idx ] = true;

                $this->mapModelDeleteRecursive[ $modelKey ] = $idx;

                break;

            case 'delete':
                $this->queue[ $idx ] = [ $model, 'delete' ];
                $this->queueArgs[ $idx ] = [];
                $this->queueModelDelete[ $idx ] = true;

                $this->mapModelDelete[ $modelKey ] = $idx;

                break;

        endswitch;
    }


    public function persistEloquentQueryForInsert(EloquentModelQueryBuilderBase $query, array $values) : void
    {
        $idx = count($this->queue);

        $this->queue[ $idx ] = [ $query, 'insert' ];
        $this->queueArgs[ $idx ] = [ $values ];
        $this->queueEloquentInsert[ $idx ] = true;
    }

    public function persistEloquentQueryForUpdate(EloquentModelQueryBuilderBase $query, array $values) : void
    {
        $idx = count($this->queue);

        $this->queue[ $idx ] = [ $query, 'update' ];
        $this->queueArgs[ $idx ] = [ $values ];
        $this->queueEloquentUpdate[ $idx ] = true;
    }

    public function persistEloquentQueryForDelete(EloquentModelQueryBuilderBase $query) : void
    {
        $idx = count($this->queue);

        $this->queue[ $idx ] = [ $query, 'delete' ];
        $this->queueArgs[ $idx ] = [];
        $this->queueEloquentDelete[ $idx ] = true;
    }


    public function persistQueryForInsert(EloquentPdoQueryBuilderBase $query, array $values) : void
    {
        $idx = count($this->queue);

        $this->queue[ $idx ] = [ $query, 'insert' ];
        $this->queueArgs[ $idx ] = [ $values ];
        $this->queueQueryInsert[ $idx ] = true;
    }

    public function persistQueryForUpdate(EloquentPdoQueryBuilderBase $query, array $values) : void
    {
        $idx = count($this->queue);

        $this->queue[ $idx ] = [ $query, 'update' ];
        $this->queueArgs[ $idx ] = [ $values ];
        $this->queueQueryUpdate[ $idx ] = true;
    }

    public function persistQueryForDelete(EloquentPdoQueryBuilderBase $query) : void
    {
        $idx = count($this->queue);

        $this->queue[ $idx ] = [ $query, 'delete' ];
        $this->queueArgs[ $idx ] = [];
        $this->queueQueryDelete[ $idx ] = true;
    }


    public function persistSqlStatement(?Connection $conn, string $sql, array $bindings) : void
    {
        $conn = $conn ?? $this->eloquent->getConnection();

        $idx = count($this->queue);

        $this->queue[ $idx ] = [ $conn, 'statement' ];
        $this->queueArgs[ $idx ] = [ $sql, $bindings ];
        $this->queueSqlStatement[ $idx ] = true;
    }


    public function flush() : array
    {
        $result = $this->doFlush();

        return $result;
    }

    private function doFlush() : array
    {
        $results = [];

        $connections = $this->flushExtractConnections();

        $this->flushBeginTransaction($connections);

        foreach ( $this->queue as $idx => $fn ) {
            try {
                $result = call_user_func_array($fn, $this->queueArgs[ $idx ]);

                $results[ $idx ] = $result;
            }
            catch ( \Throwable $e ) {
                $this->flushRollbackTransaction($connections);

                throw new DatabaseException(
                    [ 'DATABASE_ERROR', 'results' => $results ], $e
                );
            }
        }

        $this->flushCommitTransaction($connections);

        $this->reset();

        return $results;
    }


    protected function connectionKey(Connection $conn) : string
    {
        return get_class($conn) . '#' . spl_object_id($conn);
    }

    protected function modelKey(Model $model) : string
    {
        return get_class($model) . '#' . spl_object_id($model);
    }

    protected function relationKey(Relation $relation) : string
    {
        return get_class($relation) . '#' . spl_object_id($relation);
    }


    /**
     * @return Connection[]
     */
    protected function flushExtractConnections() : array
    {
        $connections = [];

        foreach ( $this->queue as $idx => $fn ) {
            $connection = null;

            if (false
                || isset($this->queueBelongsToManySave[ $idx ])
                || isset($this->queueBelongsToManySaveMany[ $idx ])
                || isset($this->queueBelongsToManySync[ $idx ])
            ) {
                /** @var BelongsToMany $relation */

                [ $relation ] = $this->queue[ $idx ];

                $connection = $relation->getQuery()->getConnection();
            }

            if (false
                || isset($this->queueHasOneOrManySave[ $idx ])
                || isset($this->queueHasOneOrManySaveMany[ $idx ])
            ) {
                /** @var HasOneOrMany $relation */

                [ $relation ] = $this->queue[ $idx ];

                $connection = $relation->getQuery()->getConnection();
            }

            if (false
                || isset($this->queueModelSaveRecursive[ $idx ])
                || isset($this->queueModelDeleteRecursive[ $idx ])
                || isset($this->queueModelSave[ $idx ])
                || isset($this->queueModelDelete[ $idx ])
            ) {
                /** @var Model $model */

                [ $model ] = $this->queue[ $idx ];

                $connection = $model->getConnection();
            }

            if (false
                || isset($this->queueEloquentInsert[ $idx ])
                || isset($this->queueEloquentUpdate[ $idx ])
                || isset($this->queueEloquentDelete[ $idx ])
            ) {
                /** @var EloquentModelQueryBuilderBase $query */

                [ $query ] = $this->queueArgs[ $idx ];

                $connection = $query->getConnection();
            }

            if (false
                || isset($this->queueQueryInsert[ $idx ])
                || isset($this->queueQueryUpdate[ $idx ])
                || isset($this->queueQueryDelete[ $idx ])
            ) {
                /** @var EloquentPdoQueryBuilderBase $query */

                [ $query ] = $this->queue[ $idx ];

                $connection = $query->getConnection();
            }

            if (isset($this->queueSqlStatement[ $idx ])) {
                [ $connection ] = $this->queue[ $idx ];
            }

            $connectionKey = $this->connectionKey($connection);

            $connections[ $connectionKey ] = $connection;
        }

        return $connections;
    }

    /**
     * @param Connection[] $connections
     *
     * @return void
     */
    protected function flushBeginTransaction(array $connections) : void
    {
        $throwables = [];

        foreach ( $connections as $connection ) {
            try {
                $connection->beginTransaction();
            }
            catch ( \Throwable $e ) {
                $throwables[] = $e;
            }
        }

        if ($throwables) {
            $ee = new DatabaseException();

            foreach ( $throwables as $t ) {
                $ee->addPrevious($t);
            }

            throw $ee;
        }
    }

    /**
     * @param Connection[] $connections
     *
     * @return void
     */
    protected function flushCommitTransaction(array $connections) : void
    {
        $throwables = [];

        foreach ( $connections as $connection ) {
            try {
                $connection->commit();
            }
            catch ( \Throwable $e ) {
                $throwables[] = $e;
            }
        }

        if ($throwables) {
            $ee = new DatabaseException();

            foreach ( $throwables as $t ) {
                $ee->addPrevious($t);
            }

            throw $ee;
        }
    }

    /**
     * @param Connection[] $connections
     *
     * @return void
     */
    protected function flushRollbackTransaction(array $connections) : void
    {
        $throwables = [];

        foreach ( $connections as $connection ) {
            try {
                $connection->rollBack();
            }
            catch ( \Throwable $e ) {
                $throwables[] = $e;
            }
        }

        if ($throwables) {
            $ee = new DatabaseException();

            foreach ( $throwables as $t ) {
                $ee->addPrevious($t);
            }

            throw $ee;
        }
    }
}
