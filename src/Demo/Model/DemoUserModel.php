<?php

namespace Gzhegow\Orm\Demo\Model;

use Gzhegow\Orm\Core\Model\Traits\Has\HasIdTrait;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModel;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Relations\MorphMany;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Relations\MorphToMany;


/**
 * @property string           $name
 *
 * @property DemoImageModel[] $_demoImages
 * @property DemoTagModel[]   $_demoTags
 */
class DemoUserModel extends EloquentModel
{
    use HasIdTrait;


    protected static function relationClasses() : array
    {
        return [
            '_demoImages' => MorphMany::class,
            '_demoTags'   => MorphToMany::class,
        ];
    }

    public function _demoImages() : MorphMany
    {
        return $this->relation()
            ->morphMany(
                __FUNCTION__,
                DemoImageModel::class,
                'imageable',
                'imageable_type',
                'imageable_id',
                'id'
            )
        ;
    }

    public function _demoTags() : MorphToMany
    {
        return $this->relation()
            ->morphToMany(
                __FUNCTION__,
                DemoTagModel::class,
                'taggable',
                DemoTagModel::tableMorphedByMany('taggable'),
                'taggable_id',
                'tag_id',
                'id',
                'id',
                false
            )
        ;
    }
}
