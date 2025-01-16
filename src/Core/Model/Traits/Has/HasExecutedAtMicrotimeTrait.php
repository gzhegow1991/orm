<?php

namespace Gzhegow\Orm\Core\Model\Traits\Has;

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
            $_executedAt = Calendar::dateTimeImmutable($_executedAtMicrotime);
            $_executedAtMicrotime = Calendar::formatMicroseconds($_executedAt);
        }

        $this->attributes[ 'executed_at_microtime' ] = $_executedAtMicrotime;
    }

    public function setupExecutedAtMicrotime($executedAtMicrotime = null) : string
    {
        $current = $this->attributes[ 'executed_at_microtime' ] ?? null;

        if (null === $current) {
            if (null === $executedAtMicrotime) {
                $_executedAt = Calendar::nowImmutable();

            } else {
                $_executedAt = Calendar::dateTimeImmutable($executedAtMicrotime);
            }

            $this->attributes[ 'executed_at_microtime' ] = Calendar::formatMicroseconds($_executedAt);
        }

        return $this->executed_at_microtime;
    }
}
