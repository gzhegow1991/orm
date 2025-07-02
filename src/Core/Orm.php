<?php

namespace Gzhegow\Orm\Core;

use Gzhegow\Orm\Core\Persistence\EloquentPersistenceInterface;
use Gzhegow\Orm\Package\Illuminate\Database\Capsule\EloquentInterface;


class Orm
{
    private function __construct()
    {
    }


    public static function factory() : OrmFactoryInterface
    {
        return static::$facade->getFactory();
    }


    public static function eloquent() : EloquentInterface
    {
        return static::$facade->getEloquent();
    }

    public static function persistence() : EloquentPersistenceInterface
    {
        return static::$facade->getPersistence();
    }


    /**
     * @template T of (\Closure(array|null $relationFn, string|null $fields) : T|string)
     *
     * @param callable|array|null $relationFn
     *
     * @return T
     */
    public static function relationDot(
        ?array $relationFn = null,
        ?string $fields = null
    )
    {
        return static::$facade->fnEloquentRelationDotnameCurry($relationFn, $fields);
    }


    public static function setFacade(?OrmInterface $facade) : ?OrmInterface
    {
        $last = static::$facade;

        static::$facade = $facade;

        return $last;
    }

    /**
     * @var OrmInterface
     */
    protected static $facade;
}
