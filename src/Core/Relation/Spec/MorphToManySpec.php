<?php

namespace Gzhegow\Database\Core\Relation\Spec;

use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelQueryBuilder;


/**
 * @property string                             $relationName
 *
 * @property EloquentModel                      $thisModel
 * @property EloquentModel                      $remoteModel
 * @property EloquentModelQueryBuilder          $remoteModelQuery
 *
 * @property string|class-string<EloquentModel> $remoteClassNameOrTableName
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
    protected $relationName = _UNDEFINED;

    /**
     * @var EloquentModel
     */
    protected $thisModel = _UNDEFINED;
    /**
     * @var EloquentModel
     */
    protected $remoteModel = _UNDEFINED;
    /**
     * @var EloquentModelQueryBuilder
     */
    protected $remoteModelQuery = _UNDEFINED;

    /**
     * @var string|class-string<EloquentModel>
     */
    protected $remoteClassNameOrTableName = _UNDEFINED;

    /**
     * @var string
     */
    protected $morphTypeName = _UNDEFINED;
    /**
     * @var string|null
     */
    protected $morphTable = _UNDEFINED;

    /**
     * @var string|null
     */
    protected $thisTableRightKey = _UNDEFINED;
    /**
     * @var string|null
     */
    protected $pivotTableLeftKey = _UNDEFINED;
    /**
     * @var string|null
     */
    protected $pivotTableRightKey = _UNDEFINED;
    /**
     * @var string|null
     */
    protected $remoteTableLeftKey = _UNDEFINED;

    /**
     * @var bool
     */
    protected $inverse = _UNDEFINED;
}
