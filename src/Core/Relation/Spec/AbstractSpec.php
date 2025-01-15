<?php

namespace Gzhegow\Orm\Core\Relation\Spec;

use Gzhegow\Lib\Lib;
use Gzhegow\Orm\Exception\RuntimeException;


abstract class AbstractSpec
{
    public function __isset($name)
    {
        if (! property_exists($this, $name)) {
            return false;
        }

        if (Lib::bool()->is_undefined($this->{$name})) {
            return false;
        }

        return true;
    }

    public function __get($name)
    {
        if (! property_exists($this, $name)) {
            throw new RuntimeException([ 'Missing property', $name ]);
        }

        $value = $this->{$name};

        if (Lib::bool()->is_undefined($value)) {
            throw new RuntimeException([ 'Value is undefined', $name ]);
        }

        return $value;
    }

    public function __set($name, $value)
    {
        if (! property_exists($this, $name)) {
            throw new RuntimeException([ 'Missing property', $name ]);
        }

        $this->{$name} = $value;
    }

    public function __unset($name)
    {
        throw new RuntimeException([ 'Unable to unset property', $name ]);
    }
}
