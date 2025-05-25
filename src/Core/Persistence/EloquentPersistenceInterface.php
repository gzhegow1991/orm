<?php

namespace Gzhegow\Orm\Core\Persistence;


use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\Builder as EloquentPdoQueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentModelQueryBuilder;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Base\EloquentModel;


interface EloquentPersistenceInterface
{
    public function reset() : void;


    public function persistBelongsToManyForSave(
        BelongsToMany $relation,
        EloquentModel $model, array $pivotAttributes = [], ?bool $touch = null
    ) : void;

    public function persistBelongsToManyForSaveMany(
        BelongsToMany $relation,
        $models, array $pivotAttributes = []
    ) : void;

    public function persistBelongsToManyForSync(
        BelongsToMany $relation,
        $ids, ?bool $detaching = null
    ) : void;


    public function persistHasOneOrManyForSave(
        HasOneOrMany $relation,
        EloquentModel $model
    ) : void;

    public function persistHasOneOrManyForSaveMany(
        HasOneOrMany $relation,
        $models
    ) : void;


    public function persistModelForSaveRecursive(EloquentModel $model) : void;

    public function persistModelForDeleteRecursive(EloquentModel $model) : void;


    public function persistModelForSave(EloquentModel $model) : void;

    public function persistModelForDelete(EloquentModel $model) : void;


    public function persistEloquentQueryForInsert(EloquentModelQueryBuilder $query, array $values) : void;

    public function persistEloquentQueryForUpdate(EloquentModelQueryBuilder $query, array $values) : void;

    public function persistEloquentQueryForDelete(EloquentModelQueryBuilder $query) : void;


    public function persistQueryForInsert(EloquentPdoQueryBuilder $query, array $values) : void;

    public function persistQueryForUpdate(EloquentPdoQueryBuilder $query, array $values) : void;

    public function persistQueryForDelete(EloquentPdoQueryBuilder $query) : void;


    public function persistSqlStatement(?Connection $conn, string $sql, array $bindings) : void;


    public function flush() : array;
}
