<?php

namespace Gzhegow\Database\Core\Model\Traits\Has;

use Gzhegow\Calendar\Calendar;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;


/**
 * @mixin EloquentModel
 *
 * @property \DateTimeInterface $created_at
 */
trait HasCreatedAtTrait
{
    public function setCreatedAt($createdAt) : void
    {
        $_createdAt = Calendar::dateTimeImmutable($createdAt);

        $this->created_at = $_createdAt;
    }

    public function setupCreatedAt($createdAt = null) : string
    {
        if (null === $this->created_at) {
            if (null === $createdAt) {
                $_createdAt = Calendar::nowImmutable();

            } else {
                $_createdAt = Calendar::dateTimeImmutable($createdAt);
            }

            $this->created_at = $_createdAt;
        }

        return $this->created_at;
    }
}
