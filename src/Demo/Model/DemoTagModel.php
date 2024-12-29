<?php

namespace Gzhegow\Database\Demo\Model;

use Gzhegow\Database\Core\Model\Traits\Has\HasIdTrait;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\Relations\MorphToMany;


/**
 * @property string          $name
 *
 * @property DemoPostModel[] $_demoPosts
 * @property DemoUserModel[] $_demoUsers
 */
class DemoTagModel extends EloquentModel
{
    use HasIdTrait;


    protected static function relationClasses() : array
    {
        return [
            '_demoPosts' => MorphToMany::class,
            '_demoUsers' => MorphToMany::class,
        ];
    }

    public function _demoPosts()
    {
        return $this->relation()
            ->morphedByMany(
                __FUNCTION__,
                DemoPostModel::class,
                'taggable',
                DemoTagModel::tableMorphedByMany('taggable'),
                'tag_id',
                'taggable_id',
                'id',
                'id',
                true
            )
        ;
    }

    public function _demoUsers()
    {
        return $this->relation()
            ->morphedByMany(
                __FUNCTION__,
                DemoUserModel::class,
                'taggable',
                DemoTagModel::tableMorphedByMany('taggable'),
                'tag_id',
                'taggable_id',
                'id',
                'id',
                true
            )
        ;
    }
}
