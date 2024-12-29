<?php

namespace Gzhegow\Database\Core;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Gzhegow\Database\Core\Query\Chunks\ChunksProcessorInterface;
use Gzhegow\Database\Core\Persistence\EloquentPersistenceInterface;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;
use Gzhegow\Database\Package\Illuminate\Database\EloquentPdoQueryBuilder;
use Gzhegow\Database\Package\Illuminate\Database\Capsule\EloquentInterface;
use Gzhegow\Database\Package\Illuminate\Database\Schema\EloquentSchemaBuilder;
use Gzhegow\Database\Package\Illuminate\Database\Schema\EloquentSchemaBlueprint;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelQueryBuilder;


class Orm
{
    public static function newChunkProcessor() : ChunksProcessorInterface
    {
        return static::$facade->newChunkProcessor();
    }


    public static function newEloquentSchemaBuilder(
        ConnectionInterface $connection
    ) : EloquentSchemaBuilder
    {
        return static::$facade->newEloquentSchemaBuilder(
            $connection
        );
    }


    public static function newEloquentSchemaBlueprint(
        $table,
        \Closure $callback = null,
        $prefix = ''
    ) : EloquentSchemaBlueprint
    {
        return static::$facade->newEloquentSchemaBlueprint(
            $table,
            $callback,
            $prefix
        );
    }


    public static function newEloquentPdoQueryBuilder(
        ConnectionInterface $connection,
        Grammar $grammar = null,
        Processor $processor = null
    ) : EloquentPdoQueryBuilder
    {
        return static::$facade->newEloquentPdoQueryBuilder(
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
    public static function newEloquentModelQueryBuilder(
        EloquentPdoQueryBuilder $query,
        //
        EloquentModel $model
    ) : EloquentModelQueryBuilder
    {
        return static::$facade->newEloquentModelQueryBuilder(
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
     * @return EloquentModelCollection<T>|T[]
     */
    public static function newEloquentModelCollection(
        iterable $models = []
    ) : EloquentModelCollection
    {
        return static::$facade->newEloquentModelCollection(
            $models
        );
    }


    public static function getEloquent() : EloquentInterface
    {
        return static::$facade->getEloquent();
    }

    public static function getEloquentPersistence() : EloquentPersistenceInterface
    {
        return static::$facade->getEloquentPersistence();
    }


    public static function eloquentRelation(
        EloquentModel $model
    )
    {
        return static::$facade->eloquentRelation($model);
    }

    /**
     * @template T of (\Closure(array|null $relationFn, string|null $fields) : T|string)
     *
     * @param callable|array|null $relationFn
     *
     * @return T
     */
    public static function eloquentRelationDot(
        array $relationFn = null,
        string $fields = null
    )
    {
        return static::$facade->eloquentRelationDot($relationFn, $fields);
    }


    public static function setFacade(OrmFacadeInterface $facade) : void
    {
        static::$facade = $facade;
    }

    /**
     * @var OrmFacadeInterface
     */
    protected static $facade;
}
