<?php

namespace Gzhegow\Orm\Package\Illuminate\Database\Schema;

use Gzhegow\Lib\Lib;
use Illuminate\Database\Connection;
use Gzhegow\Orm\Core\OrmFactoryInterface;
use Illuminate\Database\Schema\Blueprint as EloquentSchemaBlueprintBase;


class EloquentSchemaBlueprint extends EloquentSchemaBlueprintBase
{
    /**
     * @var OrmFactoryInterface
     */
    protected $factory;

    /**
     * @var Connection
     */
    protected $connection;


    public function __construct(
        OrmFactoryInterface $factory,
        //
        array $arguments, array $options = []
    )
    {
        $this->factory = $factory;

        if (isset($options[ 'connection' ])) {
            $this->connection = $options[ 'connection' ];
        }

        parent::__construct(...$arguments);
    }


    protected function createIndexName($type, array $columns) : string
    {
        /** @see parent::createIndexName() */

        $theStr = Lib::str();

        $prefix = $this->connection->getTablePrefix();

        $table = "{$prefix}{$this->table}";

        $tableCut = explode('_', $table);
        $tableCut = array_map([ $theStr, 'prefix' ], $tableCut);
        $tableCut = implode('_', $tableCut);

        $typeCut = $theStr->prefix($type);

        $columnsCut = crc32(serialize($columns));

        return "{$tableCut}_{$typeCut}_{$columnsCut}";
    }
}
