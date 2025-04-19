<?php

namespace Gzhegow\Orm\Core\Model\Traits\Has;

use Gzhegow\Lib\Lib;
use Gzhegow\Calendar\Calendar;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModel;


/**
 * @mixin EloquentModel
 *
 * @property string $executed_at_microtime
 */
trait HasExecutedAtMicrotimeTrait
{
    public function setExecutedAtMicrotime($executedAtMicrotime) : void
    {
        $_executedAtMicrotime = $executedAtMicrotime;

        if (null !== $_executedAtMicrotime) {
            Lib::date()->type_idate_microtime($_executedAt, $_executedAtMicrotime);

            $_executedAtMicrotime = Lib::date()->format_usec($_executedAt);
        }

        $this->attributes[ 'executed_at_microtime' ] = $_executedAtMicrotime;
    }

    public function setupExecutedAtMicrotime($executedAtMicrotime = null) : string
    {
        $current = $this->attributes[ 'executed_at_microtime' ] ?? null;

        if (null === $current) {
            if (null === $executedAtMicrotime) {
                $_executedAt = Lib::date()->idate_now();

            } else {
                Lib::date()->type_idate_microtime($_executedAt, $executedAtMicrotime);
            }

            $this->attributes[ 'executed_at_microtime' ] = Lib::date()->format_usec($_executedAt);
        }

        return $this->executed_at_microtime;
    }
}
