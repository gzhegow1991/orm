<?php

namespace Gzhegow\Orm\Core;

use Gzhegow\Orm\Exception\LogicException;
use Gzhegow\Orm\Core\Persistence\EloquentPersistenceInterface;
use Gzhegow\Orm\Package\Illuminate\Database\Capsule\EloquentInterface;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Base\EloquentModel;


class OrmFacade implements OrmInterface
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


    public function getFactory() : OrmFactoryInterface
    {
        return $this->factory;
    }


    public function getEloquent() : EloquentInterface
    {
        return $this->eloquent;
    }

    public function getPersistence() : EloquentPersistenceInterface
    {
        return $this->eloquentPersistence;
    }


    /**
     * @template T of (\Closure(array|null $relationFn, string|null $fields) : T|string)
     *
     * @param callable|array|null $relationFn
     *
     * @return T
     */
    public function fnEloquentRelationDotnameCurry(?array $relationFn = null, ?string $fields = null)
    {
        $fn = static function ($relationFn = null, ?string $fields = null) use (&$fn) {
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
