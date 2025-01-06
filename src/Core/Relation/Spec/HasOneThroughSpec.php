<?php

namespace Gzhegow\Database\Core\Relation\Spec;

use Gzhegow\Lib\Modules\BoolModule;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelQueryBuilder;


/**
 * @property string                             $relationName
 *
 * @property EloquentModel                      $thisModel
 * @property EloquentModel                      $throughModel
 * @property EloquentModel                      $remoteModel
 * @property EloquentModelQueryBuilder          $remoteModelQuery
 *
 * @property string|class-string<EloquentModel> $remoteModelClassOrTableName
 * @property class-string<EloquentModel>        $throughModelClass
 *
 * @property string|null                        $thisTableRightKey
 * @property string|null                        $throughTableLeftKey
 * @property string|null                        $throughTableRightKey
 * @property string|null                        $remoteTableLeftKey
 */
class HasOneThroughSpec extends AbstractSpec
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
    protected $throughModel = BoolModule::UNDEFINED;
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
     * @var class-string<EloquentModel>
     */
    protected $throughModelClass = BoolModule::UNDEFINED;

    /**
     * @var string|null
     */
    protected $thisTableRightKey = BoolModule::UNDEFINED;
    /**
     * @var string|null
     */
    protected $throughTableLeftKey = BoolModule::UNDEFINED;
    /**
     * @var string|null
     */
    protected $throughTableRightKey = BoolModule::UNDEFINED;
    /**
     * @var string|null
     */
    protected $remoteTableLeftKey = BoolModule::UNDEFINED;
}
