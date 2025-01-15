<?php

namespace Gzhegow\Orm\Demo\Model;

use Gzhegow\Orm\Core\Model\Traits\Has\HasIdTrait;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModel;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Relations\BelongsTo;


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


    protected static function relationClasses() : array
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
