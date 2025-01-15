<?php

namespace Gzhegow\Orm\Demo\Model;

use Gzhegow\Orm\Core\Model\Traits\Has\HasIdTrait;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModel;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Relations\HasMany;


/**
 * @property string         $name
 *
 * @property DemoBarModel[] _demoBars
 */
class DemoFooModel extends EloquentModel
{
    use HasIdTrait;


    protected static function relationClasses() : array
    {
        return [
            '_demoBars' => HasMany::class,
        ];
    }

    public function _demoBars() : HasMany
    {
        return $this->relation()
            ->hasMany(
                __FUNCTION__,
                DemoBarModel::class
            )
        ;
    }
}
