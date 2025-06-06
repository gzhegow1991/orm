<?php

namespace Gzhegow\Orm\Core;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\Schema\Builder as EloquentSchemaBuilder;
use Gzhegow\Orm\Core\Query\Chunks\EloquentChunksProcessorInterface;
use Gzhegow\Orm\Package\Illuminate\Database\EloquentPdoQueryBuilder;
use Gzhegow\Orm\Core\Relation\Factory\EloquentRelationFactoryInterface;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Base\EloquentModel;
use Gzhegow\Orm\Package\Illuminate\Database\Schema\EloquentSchemaBlueprint;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelCollection;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelQueryBuilder;


interface OrmFactoryInterface
{
    public function newBuilder() : OrmBuilderInterface;


    public function newEloquentChunkProcessor() : EloquentChunksProcessorInterface;


    public function newEloquentRelationFactory(EloquentModel $model) : EloquentRelationFactoryInterface;


    public function newEloquentSchemaBuilder(ConnectionInterface $connection) : EloquentSchemaBuilder;

    public function newEloquentSchemaBlueprint(array $arguments) : EloquentSchemaBlueprint;


    public function newEloquentPdoQueryBuilder(
        ConnectionInterface $connection,
        ?Grammar $grammar = null,
        ?Processor $processor = null
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
    public function newEloquentModelCollection(iterable $models = []) : EloquentModelCollection;
}
