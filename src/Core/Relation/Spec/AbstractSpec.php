<?php

namespace Gzhegow\Orm\Core\Relation\Spec;

use Gzhegow\Lib\Lib;
use Gzhegow\Orm\Exception\RuntimeException;


abstract class AbstractSpec
{
    public function __isset($name)
    {
        if (! isset($this->{$name})) {
            return false;
        }

        $value = $this->{$name};

        if (! count($value)) {
            return false;
        }

        return true;
    }

    public function __get($name)
    {
        if (! isset($this->{$name})) {
            throw new RuntimeException(
                [ 'Missing property', $name ]
            );
        }

        $value = $this->{$name};

        if (! count($value)) {
            throw new RuntimeException(
                [ 'Value is undefined', $name ]
            );
        }

        return $value[ 0 ];
    }

    public function __set($name, $value)
    {
        if (! isset($this->{$name})) {
            throw new RuntimeException(
                [ 'Missing property', $name ]
            );
        }

        $this->{$name} = [ $value ];
    }

    public function __unset($name)
    {
        throw new RuntimeException(
            [ 'Unable to unset property', $name ]
        );
    }
}
