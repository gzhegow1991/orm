<?php

namespace Gzhegow\Database\Package\Illuminate\Database\Eloquent\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;


class SerializeCast implements CastsAttributes
{
    public function get($model, $key, $value, $attributes)
    {
        return unserialize($value);
    }

    public function set($model, $key, $value, $attributes)
    {
        return serialize($value);
    }
}
