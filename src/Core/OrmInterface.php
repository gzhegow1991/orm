<?php

namespace Gzhegow\Orm\Core;

use Gzhegow\Orm\Core\Persistence\EloquentPersistenceInterface;
use Gzhegow\Orm\Package\Illuminate\Database\Capsule\EloquentInterface;


interface OrmInterface
{
    public function getFactory() : OrmFactoryInterface;


    public function getEloquent() : EloquentInterface;

    public function getPersistence() : EloquentPersistenceInterface;


    /**
     * @template T of (\Closure(array|null $relationFn, string|null $fields) : T|string)
     *
     * @param callable|array|null $relationFn
     *
     * @return T
     */
    public function fnEloquentRelationDotnameCurry(?array $relationFn = null, ?string $fields = null);
}
