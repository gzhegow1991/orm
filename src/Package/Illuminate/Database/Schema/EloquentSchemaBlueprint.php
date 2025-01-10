<?php

namespace Gzhegow\Database\Package\Illuminate\Database\Schema;

use Gzhegow\Lib\Lib;
use Gzhegow\Database\Core\OrmFactoryInterface;
use Illuminate\Database\Schema\ForeignKeyDefinition;
use Illuminate\Database\Schema\Blueprint as EloquentSchemaBlueprintBase;


class EloquentSchemaBlueprint extends EloquentSchemaBlueprintBase
{
    /**
     * @var OrmFactoryInterface
     */
    protected $factory;


    public function __construct(
        OrmFactoryInterface $factory,
        //
        $table, \Closure $callback = null, $prefix = ''
    )
    {
        $this->factory = $factory;

        parent::__construct($table, $callback, $prefix);
    }


    protected function createIndexName($type, array $columns) : string
    {
        /** @see parent::createIndexName() */

        $theStr = Lib::str();

        $table = "{$this->prefix}{$this->table}";

        $tableCut = implode('_', array_map([ $theStr, 'prefix' ], explode('_', $table)));
        $typeCut = $theStr->prefix($type);
        $columnsCut = crc32(serialize($columns));

        return "{$tableCut}_{$typeCut}{$columnsCut}";
    }
}
