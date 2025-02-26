<?php

namespace Gzhegow\Orm\Core\Model\Traits\Has;

use Gzhegow\Lib\Lib;
use Gzhegow\Orm\Exception\RuntimeException;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModel;


/**
 * @mixin EloquentModel
 *
 * @property int|string $id
 */
trait HasIdTrait
{
    /**
     * @return int|string
     */
    public function getId()
    {
        $id = null
            ?? Lib::parse()->int_positive($this->attributes[ 'id' ] ?? null)
            ?? Lib::parse()->string_not_empty($this->attributes[ 'id' ] ?? null);

        if (null === $id) {
            throw new RuntimeException('The `id` is empty');
        }

        return $id;
    }

    /**
     * @return null|int|string
     */
    public function hasId()
    {
        $id = null
            ?? Lib::parse()->int_positive($this->attributes[ 'id' ] ?? null)
            ?? Lib::parse()->string_not_empty($this->attributes[ 'id' ] ?? null);

        if (null === $id) {
            return null;
        }

        return $id;
    }
}
