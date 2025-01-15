<?php

namespace Gzhegow\Orm\Package\Illuminate\Database\Capsule;

use Gzhegow\Orm\Core\Orm;
use Illuminate\Database\ConnectionInterface;
use Gzhegow\Orm\Exception\LogicException;
use Gzhegow\Orm\Exception\RuntimeException;
use Illuminate\Database\Capsule\Manager as EloquentBase;
use Gzhegow\Orm\Package\Illuminate\Database\Schema\EloquentSchemaBuilder;


class Eloquent extends EloquentBase implements
    EloquentInterface
{
    /**
     * @var string
     */
    protected static $relationPrefix;


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


    public static function getRelationPrefix() : string
    {
        return static::$relationPrefix ?? '_';
    }

    public static function setRelationPrefix(string $relationPrefix) : void
    {
        if ('' === $relationPrefix) {
            throw new LogicException(
                'The `relationPrefix` should be non-empty string'
            );
        }

        if (! preg_match('/[a-z_]/', $relationPrefix[ 0 ])) {
            throw new LogicException(
                [ "The `relationPrefix` should begins with: ~[a-z_]~ / {$relationPrefix}", $relationPrefix ]
            );
        }

        if (null !== static::$relationPrefix) {
            $var = static::$relationPrefix;

            throw new RuntimeException(
                [ "The `relationPrefix` is already defined: {$var}", $relationPrefix ]
            );
        }

        static::$relationPrefix = $relationPrefix;
    }
}
