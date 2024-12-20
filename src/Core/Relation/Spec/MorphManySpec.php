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
     * @var string
     */
    protected $morphType = _UNDEFINED;
    /**
     * @var string|null
     */
    protected $morphTypeKey = _UNDEFINED;
    /**
     * @var string|null
     */
    protected $morphIdKey = _UNDEFINED;

    /**
     * @var string|null
     */
    protected $thisTableRightKey = _UNDEFINED;
}
