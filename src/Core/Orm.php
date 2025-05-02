<?php

namespace Gzhegow\Orm\Core;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Gzhegow\Orm\Core\Query\Chunks\ChunksProcessorInterface;
use Gzhegow\Orm\Core\Persistence\EloquentPersistenceInterface;
use Illuminate\Database\Schema\Builder as EloquentSchemaBuilder;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModel;
use Gzhegow\Orm\Package\Illuminate\Database\EloquentPdoQueryBuilder;
use Gzhegow\Orm\Package\Illuminate\Database\Capsule\EloquentInterface;
use Gzhegow\Orm\Package\Illuminate\Database\Schema\EloquentSchemaBlueprint;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelCollection;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelQueryBuilder;


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


    public static function newEloquentRelationFactory(
        EloquentModel $model
    )
    {
        return static::$facade->newEloquentRelationFactory($model);
    }


    public static function eloquent() : EloquentInterface
    {
        return static::$facade->getEloquent();
    }

    public static function eloquentPersistence() : EloquentPersistenceInterface
    {
        return static::$facade->getEloquentPersistence();
    }


    /**
     * @template T of (\Closure(array|null $relationFn, string|null $fields) : T|string)
     *
     * @param callable|array|null $relationFn
     *
     * @return T
     */
    public static function relationDot(
        array $relationFn = null,
        string $fields = null
    )
    {
        return static::$facade->fnEloquentRelationDotnameCurry($relationFn, $fields);
    }


    public static function setFacade(?OrmFacadeInterface $facade) : ?OrmFacadeInterface
    {
        $last = static::$facade;

        static::$facade = $facade;

        return $last;
    }

    /**
     * @var OrmFacadeInterface
     */
    protected static $facade;
}
