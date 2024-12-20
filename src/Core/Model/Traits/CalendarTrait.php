<?php

namespace Gzhegow\Database\Core\Model\Traits;

use Gzhegow\Calendar\Calendar;
use Gzhegow\Database\Exception\RuntimeException;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;


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

        $formats = [
            Calendar::FORMAT_SQL_MICROSECONDS,
            Calendar::FORMAT_SQL_MILLISECONDS,
            Calendar::FORMAT_SQL,
            $this->getDateFormat(),
        ];

        if (null === $value) {
            return null;
        }

        $dateTimeImmutable = Calendar::parseDateTimeImmutable($value, $formats);

        if (null === $dateTimeImmutable) {
            throw new RuntimeException(
                [
                    'Unable to parse DateTime',
                    $value,
                ]
            );
        }

        return $dateTimeImmutable;
    }

    protected function asDateTimeFormat($value, string $format)
    {
        $formats = [ $format ];

        $dateTimeImmutable = Calendar::parseDateTimeImmutable($value, $formats);

        return $dateTimeImmutable;
    }


    protected function serializeDate(\DateTimeInterface $date)
    {
        /** @see HasAttributes::serializeDate() */

        return Calendar::formatJavascriptMilliseconds($date);
    }
}
