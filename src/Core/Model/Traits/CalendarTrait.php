<?php

namespace Gzhegow\Orm\Core\Model\Traits;

use Gzhegow\Lib\Lib;
use Gzhegow\Orm\Exception\RuntimeException;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModel;


/**
 * @mixin EloquentModel
 */
trait CalendarTrait
{
    protected function asDate($value)
    {
        /** @see HasAttributes::asDate() */

        $date = $this->asDateTime($value);

        $date->setTime(0, 0, 0, 0);

        return $date;
    }


    protected function asDateTime($value)
    {
        /** @see HasAttributes::asDateTime() */

        if (null === $value) {
            return null;
        }

        $formats = [
            'Y-m-d H:i:s.u',
            'Y-m-d H:i:s.v',
            'Y-m-d H:i:s',
            $this->getDateFormat(),
        ];

        $status = Lib::type()->idate_formatted(
            $dateTimeImmutable,
            $formats, $value
        );

        if (! $status) {
            throw new RuntimeException(
                [ 'Unable to parse date', $value ]
            );
        }

        return $dateTimeImmutable;
    }

    protected function asDateTimeFormat($value, string $format)
    {
        $formats = [ $format ];

        $status = Lib::type()->idate_formatted(
            $dateTimeImmutable,
            $formats, $value
        );

        if (! $status) {
            throw new RuntimeException(
                [ 'Unable to parse date', $value ]
            );
        }

        return $dateTimeImmutable;
    }


    protected function serializeDate(\DateTimeInterface $date)
    {
        /** @see HasAttributes::serializeDate() */

        return Lib::date()->format_javascript_msec($date);
    }
}
