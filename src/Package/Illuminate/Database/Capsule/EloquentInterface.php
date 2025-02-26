<?php

namespace Gzhegow\Orm\Package\Illuminate\Database\Capsule;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Schema\Builder as EloquentSchemaBuilder;


interface EloquentInterface
{
    /**
     * @param string|ConnectionInterface $connection
     *
     * @return EloquentSchemaBuilder
     */
    public function getSchemaBuilder($connection = null) : EloquentSchemaBuilder;


    public static function getRelationPrefix() : string;

    public static function setRelationPrefix(string $relationPrefix) : void;
}
