<?php

namespace Gzhegow\Database\Demo\Model;

use Gzhegow\Database\Core\Model\Traits\Has\HasIdTrait;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\Relations\HasMany;


/**
 * @property string         $name
 *
 * @property DemoBarModel[] _demoBars
 */
class DemoFooModel extends EloquentModel
{
    use HasIdTrait;


    protected function relationClasses() : array
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
