<?php

namespace Gzhegow\Database\Package\Illuminate\Database\Eloquent\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;


class Base64Cast implements CastsAttributes
{
    public function get($model, $key, $value, $attributes)
    {
        return base64_decode($value);
    }

    public function set($model, $key, $value, $attributes)
    {
        return base64_encode($value);
    }
}
