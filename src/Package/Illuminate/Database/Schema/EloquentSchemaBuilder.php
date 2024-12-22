<?php

namespace Gzhegow\Database\Package\Illuminate\Database\Schema;

use Gzhegow\Lib\Delegate\DelegateTrait;
use Illuminate\Database\Schema\Builder as EloquentSchemaBuilderBase;


/**
 * @mixin EloquentSchemaBuilderBase
 */
class EloquentSchemaBuilder
{
    use DelegateTrait;


    /**
     * @var EloquentSchemaBuilderBase
     */
    protected $delegate;


    public function __construct(EloquentSchemaBuilderBase $delegate)
    {
        $this->delegate = $delegate;
    }
}
