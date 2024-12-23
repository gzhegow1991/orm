<?php

namespace Gzhegow\Database\Package\Illuminate\Database\Schema;

use Gzhegow\Lib\Delegate\Delegate;
use Illuminate\Database\Schema\Builder as EloquentSchemaBuilderBase;


/**
 * @mixin EloquentSchemaBuilderBase
 */
class EloquentSchemaBuilder
{
    /**
     * @var EloquentSchemaBuilderBase
     */
    protected $delegate;


    public function __construct(EloquentSchemaBuilderBase $delegate)
    {
        $this->delegate = new Delegate($delegate);
    }


    public function __isset($name)
    {
        return isset($this->delegate->{$name});
    }

    public function __get($name)
    {
        return $this->delegate->{$name};
    }

    public function __set($name, $value)
    {
        $this->delegate->{$name} = $value;
    }

    public function __unset($name)
    {
        unset($this->delegate->{$name});
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([ $this->delegate, $name ], $arguments);
    }
}
