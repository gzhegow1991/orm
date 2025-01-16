<?php

namespace Gzhegow\Orm\Core\Model\Traits\Has;

use Gzhegow\Lib\Lib;
use Gzhegow\Orm\Exception\RuntimeException;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModel;


/**
 * @mixin EloquentModel
 *
 * @property string $uuid
 */
trait HasUuidTrait
{
    public function getUuid() : string
    {
        $uuid = null
            ?? Lib::parse()->string_not_empty($this->attributes[ 'uuid' ] ?? null)
            ?? Lib::php()->throw([ 'The `uuid` is empty' ]);

        return $uuid;
    }

    public function hasUuid() : ?string
    {
        return $this->attributes[ 'uuid' ] ?? null;
    }


    public function setUuid($uuid) : void
    {
        $_uuid = null
            ?? Lib::parse()->string_not_empty($uuid)
            ?? Lib::php()->throw([ 'The `uuid` should be non-empty string' ]);

        $this->attributes[ 'uuid' ] = $_uuid;
    }

    public function setupUuid($uuid = null) : string
    {
        $current = $this->attributes[ 'uuid' ] ?? null;

        if (null === $current) {
            $_uuid = null
                ?? Lib::parse()->string_not_empty($uuid)
                ?? Lib::random()->uuid();

            $this->attributes[ 'uuid' ] = $_uuid;
        }

        return $this->attributes[ 'uuid' ];
    }
}
