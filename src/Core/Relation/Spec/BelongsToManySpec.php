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
 * @property string|class-string<EloquentModel> $remoteModelClassOrTableName
 * @property class-string<EloquentModel>        $pivotModelClass
 *
 * @property string|null                        $thisTableRightKey
 * @property string|null                        $pivotTableLeftKey
 * @property string|null                        $pivotTableRightKey
 * @property string|null                        $remoteTableLeftKey
 */
class BelongsToManySpec extends AbstractSpec
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
    protected $remoteModelClassOrTableName = _UNDEFINED;
    /**
     * @var class-string<EloquentModel>
     */
    protected $pivotModelClass = _UNDEFINED;

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
}
