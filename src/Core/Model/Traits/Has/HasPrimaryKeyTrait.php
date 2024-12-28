<?php

namespace Gzhegow\Database\Core\Model\Traits\Has;

use Gzhegow\Lib\Lib;
use Gzhegow\Database\Exception\RuntimeException;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;


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
        $field = $this->getKeyName();

        $pk = null
            ?? Lib::parse()->int_positive($this->attributes[ $field ] ?? null)
            ?? Lib::parse()->string_not_empty($this->attributes[ $field ] ?? null);

        if (null === $pk) {
            throw new RuntimeException(
                "The `{$field}` is empty"
            );
        }

        return $pk;
    }

    /**
     * @return null|int|string
     */
    public function hasPrimaryKey()
    {
        $field = $this->getKeyName();

        $pk = null
            ?? Lib::parse()->int_positive($this->attributes[ $field ] ?? null)
            ?? Lib::parse()->string_not_empty($this->attributes[ $field ] ?? null);

        if (null === $pk) {
            return null;
        }

        return $pk;
    }
}
