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
 * @property  string                            $morphTypeName
 * @property  string|null                       $morphTable
 *
 * @property string|null                        $thisTableRightKey
 * @property string|null                        $pivotTableLeftKey
 * @property string|null                        $pivotTableRightKey
 * @property string|null                        $remoteTableLeftKey
 *
 * @property bool                               $inverse
 */
class MorphToManySpec extends AbstractSpec
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
    protected $morphTypeName = [];
    /**
     * @var string|null
     */
    protected $morphTable = [];

    /**
     * @var string|null
     */
    protected $thisTableRightKey = [];
    /**
     * @var string|null
     */
    protected $pivotTableLeftKey = [];
    /**
     * @var string|null
     */
    protected $pivotTableRightKey = [];
    /**
     * @var string|null
     */
    protected $remoteTableLeftKey = [];

    /**
     * @var bool
     */
    protected $inverse = [];
}
