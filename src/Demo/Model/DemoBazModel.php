<?php

namespace Gzhegow\Database\Demo\Model;

use Gzhegow\Database\Core\Model\Traits\Has\HasIdTrait;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\Relations\BelongsTo;


/**
 * @property int          demo_bar_id
 *
 * @property string       name
 *
 * @property DemoBarModel _demoBar
 */
class DemoBazModel extends EloquentModel
{
    use HasIdTrait;


    protected function relationClasses() : array
    {
        return [
            '_demoBar' => BelongsTo::class,
        ];
    }

    public function _demoBar() : BelongsTo
    {
        return $this->relation()
            ->belongsTo(
                __FUNCTION__,
                DemoBarModel::class
            )
        ;
    }
}
