<?php

namespace Gzhegow\Orm\Demo\Model;

use Gzhegow\Orm\Core\Model\Traits\Has\HasIdTrait;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModel;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Relations\MorphTo;


/**
 * @property string                      $name
 *
 * @property string                      $imageable_type
 * @property string                      $imageable_id
 *
 * @property DemoPostModel|DemoUserModel $_imageable
 */
class DemoImageModel extends EloquentModel
{
    use HasIdTrait;


    protected static function relationClasses() : array
    {
        return [
            '_imageable' => MorphTo::class,
        ];
    }

    public function _imageable()
    {
        return $this->relation()
            ->morphTo(
                __FUNCTION__,
                'imageable',
                'imageable_type',
                'imageable_id',
                'id'
            )
        ;
    }
}
