<?php

namespace Gzhegow\Orm\Core\Model\Traits\Has;

use Gzhegow\Calendar\Calendar;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModel;


/**
 * @mixin EloquentModel
 *
 * @property \DateTimeInterface $created_at
 * @property \DateTimeInterface $updated_at
 */
trait HasTimestampsTrait
{
    public function freshTimestamp()
    {
        /** @see HasTimestamps::freshTimestamp() */

        return Calendar::nowImmutable();
    }
}
