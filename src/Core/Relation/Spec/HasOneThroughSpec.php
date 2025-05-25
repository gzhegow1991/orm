<?php

namespace Gzhegow\Orm\Core\Relation\Spec;

use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Base\EloquentModel;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelQueryBuilder;


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
    protected $relationName = [];

    /**
     * @var EloquentModel
     */
    protected $thisModel = [];
    /**
     * @var EloquentModel
     */
    protected $throughModel = [];
    /**
     * @var EloquentModel
     */
    protected $remoteModel = [];
    /**
     * @var EloquentModelQueryBuilder
     */
    protected $remoteModelQuery = [];

    /**
     * @var string|class-string<EloquentModel>
     */
    protected $remoteModelClassOrTableName = [];

    /**
     * @var class-string<EloquentModel>
     */
    protected $throughModelClass = [];

    /**
     * @var string|null
     */
    protected $thisTableRightKey = [];
    /**
     * @var string|null
     */
    protected $throughTableLeftKey = [];
    /**
     * @var string|null
     */
    protected $throughTableRightKey = [];
    /**
     * @var string|null
     */
    protected $remoteTableLeftKey = [];
}
