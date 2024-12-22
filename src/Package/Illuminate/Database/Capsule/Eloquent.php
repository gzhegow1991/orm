<?php

namespace Gzhegow\Database\Package\Illuminate\Database\Capsule;

use Gzhegow\Database\Core\Orm;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Capsule\Manager as EloquentBase;
use Gzhegow\Database\Package\Illuminate\Database\Schema\EloquentSchemaBuilder;


class Eloquent extends EloquentBase implements
    EloquentInterface
{
    /**
     * @param string|ConnectionInterface $connection
     *
     * @return EloquentSchemaBuilder
     */
    public function getSchemaBuilder($connection = null) : EloquentSchemaBuilder
    {
        $_connection = is_object($connection)
            ? $connection
            : $this->getConnection($connection);

        $schema = Orm::newEloquentSchemaBuilder($_connection);

        return $schema;
    }
}
