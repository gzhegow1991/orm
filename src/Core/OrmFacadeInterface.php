<?php

namespace Gzhegow\Database\Core;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Gzhegow\Database\Core\Persistence\EloquentPersistenceInterface;
use Gzhegow\Database\Core\Relation\Factory\RelationFactoryInterface;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;
use Gzhegow\Database\Package\Illuminate\Database\EloquentPdoQueryBuilder;
use Gzhegow\Database\Package\Illuminate\Database\Capsule\EloquentInterface;
use Gzhegow\Database\Package\Illuminate\Database\Schema\EloquentSchemaBuilder;
use Gzhegow\Database\Package\Illuminate\Database\Schema\EloquentSchemaBlueprint;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelQueryBuilder;


interface OrmFacadeInterface
{
    public function newEloquentSchemaBuilder(
        ConnectionInterface $connection
    ) : EloquentSchemaBuilder;

    public function newEloquentSchemaBlueprint(
        $table,
        \Closure $callback = null,
        $prefix = ''
    ) : EloquentSchemaBlueprint;


    public function newEloquentPdoQueryBuilder(
        ConnectionInterface $connection,
        Grammar $grammar = null,
        Processor $processor = null
    ) : EloquentPdoQueryBuilder;

    /**
     * @template-covariant T of EloquentModel
     *
     * @param T $model
     *
     * @return EloquentModelQueryBuilder<T>
     */
    public function newEloquentModelQueryBuilder(
        EloquentPdoQueryBuilder $query,
        //
        EloquentModel $model
    ) : EloquentModelQueryBuilder;


    /**
     * @template-covariant T of EloquentModel
     *
     * @param iterable<T> $models
     *
     * @return EloquentModelCollection<T>|T[]
     */
    public function newEloquentModelCollection(
        iterable $models = []
    ) : EloquentModelCollection;


    public function getEloquent() : EloquentInterface;

    public function getEloquentPersistence() : EloquentPersistenceInterface;


    public function eloquentRelation(
        EloquentModel $model
    ) : RelationFactoryInterface;

    public function eloquentRelationPrefix() : string;

    /**
     * @template T of (\Closure(array|null $relationFn, string|null $fields) : T|string)
     *
     * @param callable|array|null $relationFn
     *
     * @return T
     */
    public function eloquentRelationDot(array $relationFn = null, string $fields = null);
}
