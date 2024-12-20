<?php

namespace Gzhegow\Database\Package\Illuminate\Database\Eloquent\Casts;

use Gzhegow\Lib\Lib;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;


class Base64SerializeCast implements CastsAttributes
{
    public function get($model, $key, $value, $attributes)
    {
        return unserialize(base64_decode($value));
    }

    public function set($model, $key, $value, $attributes)
    {
        return base64_encode(serialize($value));
    }
}
