<?php

namespace Gzhegow\Orm\Package\Illuminate\Database\Eloquent;

use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Base\EloquentModel as EloquentModelBase;


abstract class EloquentModel extends EloquentModelBase
{
    public function offsetExists($offset) : bool
    {
        return $this->_offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->_offsetGet($offset);
    }

    public function offsetSet($offset, $value) : void
    {
        $this->_offsetSet($offset, $value);
    }

    public function offsetUnset($offset) : void
    {
        $this->offsetUnset($offset);
    }


    public function jsonSerialize() : mixed
    {
        return $this->_jsonSerialize();
    }
}
