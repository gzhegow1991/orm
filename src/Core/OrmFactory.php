<?php

namespace Gzhegow\Database\Core;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\Schema\Builder as EloquentSchemaBuilder;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;
use Gzhegow\Database\Package\Illuminate\Database\EloquentPdoQueryBuilder;
use Gzhegow\Database\Package\Illuminate\Database\Schema\EloquentSchemaBlueprint;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelQueryBuilder;


class OrmFactory implements OrmFactoryInterface
{
    public function newEloquentSchemaBuilder(
        ConnectionInterface $connection
    ) : EloquentSchemaBuilder
    {
        $schema = $connection->getSchemaBuilder();

        $schema->blueprintResolver(
            function ($table, \Closure $callback = null, string $prefix = '') {
                $blueprint = $this->newEloquentSchemaBlueprint(
                    $table,
                    $callback,
                    $prefix
                );

                return $blueprint;
            }
        );

        return $schema;
    }

    public function newEloquentSchemaBlueprint(
        $table,
        \Closure $callback = null,
        $prefix = ''
    ) : EloquentSchemaBlueprint
    {
        return new EloquentSchemaBlueprint(
            $this,
            //
            $table,
            $callback,
            $prefix
        );
    }


    public function newEloquentPdoQueryBuilder(
        ConnectionInterface $connection,
        Grammar $grammar = null,
        Processor $processor = null
    ) : EloquentPdoQueryBuilder
    {
        return new EloquentPdoQueryBuilder(
            $connection,
            $grammar,
            $processor
        );
    }

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
    ) : EloquentModelQueryBuilder
    {
        return new EloquentModelQueryBuilder(
            $query,
            //
            $model
        );
    }


    /**
     * @template-covariant T of EloquentModel
     *
     * @param iterable<T> $models
     *
     * @return EloquentModelCollection<T>
     */
    public function newEloquentModelCollection(
        iterable $models = []
    ) : EloquentModelCollection
    {
        $items = [];
        foreach ( $models as $i => $model ) {
            $items[ $i ] = $model;
        }

        return new EloquentModelCollection($items);
    }
}
