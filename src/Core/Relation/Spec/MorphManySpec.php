<?php

namespace Gzhegow\Orm\Core\Relation\Spec;

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
    protected $relationName = [];

    /**
     * @var EloquentModel
     */
    protected $thisModel = [];
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
     * @var string
     */
    protected $morphType = [];
    /**
     * @var string|null
     */
    protected $morphTypeKey = [];
    /**
     * @var string|null
     */
    protected $morphIdKey = [];

    /**
     * @var string|null
     */
    protected $thisTableRightKey = [];
}
