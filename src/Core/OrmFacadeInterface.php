<?php

namespace Gzhegow\Orm\Core;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Gzhegow\Orm\Core\Persistence\EloquentPersistenceInterface;
use Illuminate\Database\Schema\Builder as EloquentSchemaBuilder;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModel;
use Gzhegow\Orm\Package\Illuminate\Database\EloquentPdoQueryBuilder;
use Gzhegow\Orm\Package\Illuminate\Database\Capsule\EloquentInterface;
use Gzhegow\Orm\Core\Relation\Factory\EloquentRelationFactoryInterface;
use Gzhegow\Orm\Package\Illuminate\Database\Schema\EloquentSchemaBlueprint;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelCollection;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelQueryBuilder;


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


    public function newEloquentRelationFactory(
        EloquentModel $model
    ) : EloquentRelationFactoryInterface;


    public function getEloquent() : EloquentInterface;

    public function getEloquentPersistence() : EloquentPersistenceInterface;


    /**
     * @template T of (\Closure(array|null $relationFn, string|null $fields) : T|string)
     *
     * @param callable|array|null $relationFn
     *
     * @return T
     */
    public function fnEloquentRelationDotnameCurry(array $relationFn = null, string $fields = null);
}
