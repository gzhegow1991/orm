<?php

namespace Gzhegow\Database\Demo\Model;

use Gzhegow\Database\Core\Model\Traits\Has\HasIdTrait;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\Relations\HasMany;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\Relations\BelongsTo;


/**
 * @property int            demo_foo_id
 *
 * @property string         name
 *
 * @property DemoFooModel   _demoFoo
 * @property DemoBazModel[] _demoBazs
 */
class DemoBarModel extends EloquentModel
{
    use HasIdTrait;


    protected function relationClasses() : array
    {
        return [
            '_demoFoo'  => BelongsTo::class,
            '_demoBazs' => HasMany::class,
        ];
    }

    public function _demoFoo() : BelongsTo
    {
        return $this->relation()
            ->belongsTo(
                __FUNCTION__,
                DemoFooModel::class
            )
        ;
    }

    public function _demoBazs() : HasMany
    {
        return $this->relation()
            ->hasMany(
                __FUNCTION__,
                DemoBazModel::class
            )
        ;
    }
}
