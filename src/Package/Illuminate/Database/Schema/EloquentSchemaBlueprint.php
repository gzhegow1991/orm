<?php

namespace Gzhegow\Database\Package\Illuminate\Database\Schema;

use Gzhegow\Database\Core\OrmFactoryInterface;
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
}
