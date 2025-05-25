<?php

namespace Gzhegow\Orm\Core\Model\Traits\Has;

use Gzhegow\Lib\Lib;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Base\EloquentModel;


/**
 * @mixin EloquentModel
 *
 * @property \DateTimeInterface $created_at
 */
trait HasCreatedAtTrait
{
    public function setCreatedAt($createdAt) : void
    {
        $_createdAt = $createdAt;

        if (null !== $_createdAt) {
            Lib::date()->type_idate($_createdAt, $_createdAt);
        }

        $this->attributes[ 'created_at' ] = $_createdAt;
    }

    public function setupCreatedAt($createdAt = null) : string
    {
        $current = $this->attributes[ 'created_at' ] ?? null;

        if (null === $current) {
            if (null === $createdAt) {
                $_createdAt = Lib::date()->idate_now();

            } else {
                Lib::date()->type_idate($_createdAt, $createdAt);
            }

            $this->attributes[ 'created_at' ] = $_createdAt;
        }

        return $this->created_at;
    }
}
