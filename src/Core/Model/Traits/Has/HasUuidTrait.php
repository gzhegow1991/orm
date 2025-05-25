<?php

namespace Gzhegow\Orm\Core\Model\Traits\Has;

use Gzhegow\Lib\Lib;
use Gzhegow\Orm\Exception\LogicException;
use Gzhegow\Orm\Exception\RuntimeException;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Base\EloquentModel;


/**
 * @mixin EloquentModel
 *
 * @property string $uuid
 */
trait HasUuidTrait
{
    public function getUuid() : string
    {
        if (! Lib::type()->string_not_empty($_uuid, $this->attributes[ 'uuid' ])) {
            throw new RuntimeException('The `uuid` is empty');
        }

        return $_uuid;
    }

    public function hasUuid() : ?string
    {
        return $this->attributes[ 'uuid' ] ?? null;
    }


    public function setUuid($uuid) : void
    {
        if (! Lib::type()->string_not_empty($_uuid, $uuid)) {
            throw new LogicException('The `uuid` should be non-empty string');
        }

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
