<?php

namespace Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Casts;

use Gzhegow\Lib\Lib;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;


class Base64JsonCast implements CastsAttributes
{
    public function get($model, $key, $value, $attributes)
    {
        return Lib::json()->json_decode(base64_decode($value), null, []);
    }

    public function set($model, $key, $value, $attributes)
    {
        return base64_encode(Lib::json()->json_encode($value, []));
    }
}
