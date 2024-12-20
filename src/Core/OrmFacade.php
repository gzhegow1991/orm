<?php

namespace Gzhegow\Database\Core;

use Illuminate\Database\ConnectionInterface;
use Gzhegow\Database\Exception\LogicException;
use Illuminate\Database\Query\Grammars\Grammar;
use Gzhegow\Database\Core\Relation\Factory\RelationFactory;
use Illuminate\Database\Query\Processors\Processor;
use Gzhegow\Database\Core\Relation\Factory\RelationFactoryInterface;
use Illuminate\Database\Schema\Builder as EloquentSchemaBuilder;
use Gzhegow\Database\Core\Persistence\EloquentPersistenceInterface;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;
use Gzhegow\Database\Package\Illuminate\Database\EloquentPdoQueryBuilder;
use Gzhegow\Database\Package\Illuminate\Database\Capsule\EloquentInterface;
use Gzhegow\Database\Package\Illuminate\Database\Schema\EloquentSchemaBlueprint;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelQueryBuilder;


class OrmFacade implements OrmFacadeInterface
{
    /**
     * @var OrmFactoryInterface
     */
    protected $factory;

    /**
     * @var EloquentInterface
     */
    protected $eloquent;
    /**
     * @var EloquentPersistenceInterface
     */
    protected $eloquentPersistence;


    public function __construct(
        OrmFactoryInterface $factory,
        //
        EloquentInterface $eloquent,
        EloquentPersistenceInterface $eloquentPersistence
    )
    {
        $this->factory = $factory;

        $this->eloquent = $eloquent;
        $this->eloquentPersistence = $eloquentPersistence;
    }


    public function newEloquentSchemaBuilder(
        ConnectionInterface $connection
    ) : EloquentSchemaBuilder
    {
        return $this->factory->newEloquentSchemaBuilder(
            $connection
        );
    }

    public function newEloquentSchemaBlueprint(
        $table,
        \Closure $callback = null,
        $prefix = ''
    ) : EloquentSchemaBlueprint
    {
        return $this->factory->newEloquentSchemaBlueprint(
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
        return $this->factory->newEloquentPdoQueryBuilder(
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
        return $this->factory->newEloquentModelQueryBuilder(
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
        return $this->factory->newEloquentModelCollection(
            $models
        );
    }


    public function getEloquent() : EloquentInterface
    {
        return $this->eloquent;
    }

    public function getEloquentPersistence() : EloquentPersistenceInterface
    {
        return $this->eloquentPersistence;
    }


    public function eloquentRelation(
        EloquentModel $model
    ) : RelationFactoryInterface
    {
        return new RelationFactory($model);
    }

    /**
     * @template T of (\Closure(array|null $relationFn, string|null $fields) : T|string)
     *
     * @param callable|array|null $relationFn
     *
     * @return T
     */
    public function eloquentRelationDot(array $relationFn = null, string $fields = null)
    {
        $fn = static function ($relationFn = null, string $fields = null) use (&$fn) {
            static $current;

            if (null === $relationFn) {
                // return ltrim($current, '.');
                return substr($current, 1);
            }

            if (true
                && is_subclass_of($relationFn[ 0 ], EloquentModel::class)
                && method_exists($relationFn[ 0 ], $relationFn[ 1 ])
            ) {
                $current .= '.' . $relationFn[ 1 ];

                if (null !== $fields) {
                    $current .= ':' . $fields;
                }

            } else {
                throw new LogicException(
                    [
                        'The `relationFn` should be valid callable-array of existing relation',
                        $relationFn,
                    ]
                );
            }

            return $fn;
        };

        return (null !== $relationFn)
            ? $fn($relationFn, $fields)
            : $fn;
    }
}
