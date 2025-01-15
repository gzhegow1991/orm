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
        if (null === $executedAtMicrotime) {
            $_executedAtMicrotime = $executedAtMicrotime;

        } else {
            $_executedAt = Calendar::dateTimeImmutable($executedAtMicrotime);

            $_executedAtMicrotime = Calendar::formatMicroseconds($_executedAt);
        }

        $this->executed_at_microtime = $_executedAtMicrotime;
    }

    public function setupExecutedAtMicrotime($executedAtMicrotime = null) : string
    {
        if (null === $this->executed_at_microtime) {
            if (null === $executedAtMicrotime) {
                $_executedAt = Calendar::nowImmutable();

            } else {
                $_executedAt = Calendar::dateTimeImmutable($executedAtMicrotime);
            }

            $this->executed_at_microtime = Calendar::formatMicroseconds($_executedAt);
        }

        return $this->executed_at_microtime;
    }
}
