<?php

namespace Gzhegow\Orm\Core\Relation\Spec;

use Gzhegow\Lib\Modules\BoolModule;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModel;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelQueryBuilder;


/**
 * @property string                       $relationName
 *
 * @property EloquentModel                $thisModel
 * @property EloquentModel                $morphModel
 * @property EloquentModelQueryBuilder    $morphModelQuery
 *
 * @property  string|null                 $morphType
 * @property  string|null                 $morphTypeKey
 * @property  string|null                 $morphIdKey
 *
 * @property  class-string<EloquentModel> $morphClass
 *
 * @property string|null                  $remoteTableLeftKey
 *
 * @property bool                         $inverse
 */
class MorphToSpec extends AbstractSpec
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
    protected $morphModel = BoolModule::UNDEFINED;
    /**
     * @var EloquentModelQueryBuilder
     */
    protected $morphModelQuery = BoolModule::UNDEFINED;

    /**
     * @var string|null
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
     * @var class-string<EloquentModel>
     */
    protected $morphClass = BoolModule::UNDEFINED;

    /**
     * @var string|null
     */
    protected $remoteTableLeftKey = BoolModule::UNDEFINED;
}
