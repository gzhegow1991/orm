<?php

namespace Gzhegow\Orm\Core\Model\Traits\Has;

use Gzhegow\Lib\Lib;
use Gzhegow\Orm\Exception\RuntimeException;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Base\EloquentModel;


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
        $theType = Lib::type();

        $id = $this->attributes[ 'id' ] ?? null;

        $status = false
            || $theType->int_positive($idValid, $id)
            || $theType->string_not_empty($idValid, $id);

        if (! $status) {
            throw new RuntimeException('The `id` is empty');
        }

        return $idValid;
    }

    /**
     * @return null|int|string
     */
    public function hasId()
    {
        $theType = Lib::type();

        $id = $this->attributes[ 'id' ] ?? null;

        $status = false
            || $theType->int_positive($idValid, $id)
            || $theType->string_not_empty($idValid, $id);

        if (! $status) {
            return null;
        }

        return $idValid;
    }
}
