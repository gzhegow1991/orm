<?php

namespace Gzhegow\Orm\Core\Relation\Spec;

use Gzhegow\Lib\Modules\BoolModule;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModel;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelQueryBuilder;


/**
 * @property string                             $relationName
 *
 * @property EloquentModel                      $thisModel
 * @property EloquentModel                      $remoteModel
 * @property EloquentModelQueryBuilder          $remoteModelQuery
 *
 * @property string|class-string<EloquentModel> $remoteModelClassOrTableName
 *
 * @property string|null                        $morphType
 * @property string|null                        $morphTypeKey
 * @property string|null                        $morphIdKey
 *
 * @property string|null                        $thisTableRightKey
 */
class MorphManySpec extends AbstractSpec
{
    /**
     * @var string
     */
    protected $relationName = BoolModule::UNDEFINED;

    /**
     * @var EloquentModel
     */
    protected $thisModel = BoolModule::UNDEFINED;
    /**
     * @var EloquentModel
     */
    protected $remoteModel = BoolModule::UNDEFINED;
    /**
     * @var EloquentModelQueryBuilder
     */
    protected $remoteModelQuery = BoolModule::UNDEFINED;

    /**
     * @var string|class-string<EloquentModel>
     */
    protected $remoteModelClassOrTableName = BoolModule::UNDEFINED;

    /**
     * @var string
     */
    protected $morphType = BoolModule::UNDEFINED;
    /**
     * @var string|null
     */
    protected $morphTypeKey = BoolModule::UNDEFINED;
    /**
     * @var string|null
     */
    protected $morphIdKey = BoolModule::UNDEFINED;

    /**
     * @var string|null
     */
    protected $thisTableRightKey = BoolModule::UNDEFINED;
}
