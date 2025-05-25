<?php

namespace Gzhegow\Orm\Core\Model\Traits\Has;

use Gzhegow\Lib\Lib;
use Gzhegow\Orm\Exception\RuntimeException;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Base\EloquentModel;


/**
 * @mixin EloquentModel
 */
trait HasPrimaryKeyTrait
{
    /**
     * @return int|string
     */
    public function getPrimaryKey()
    {
        $theType = Lib::type();

        $field = $this->getKeyName();

        $pk = $this->attributes[ $field ] ?? null;

        $status = false
            || $theType->int_positive($pkValid, $pk)
            || $theType->string_not_empty($pkValid, $pk);

        if (! $status) {
            throw new RuntimeException("The `{$field}` is empty");
        }

        return $pkValid;
    }

    /**
     * @return null|int|string
     */
    public function hasPrimaryKey()
    {
        $theType = Lib::type();

        $field = $this->getKeyName();

        $pk = $this->attributes[ $field ] ?? null;

        $status = false
            || $theType->int_positive($pkValid, $pk)
            || $theType->string_not_empty($pkValid, $pk);

        if (! $status) {
            return null;
        }

        return $pkValid;
    }
}
