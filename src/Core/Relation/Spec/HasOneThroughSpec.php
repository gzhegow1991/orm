<?php

namespace Gzhegow\Database\Core\Relation\Spec;

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
    protected $relationName = _UNDEFINED;

    /**
     * @var EloquentModel
     */
    protected $thisModel = _UNDEFINED;
    /**
     * @var EloquentModel
     */
    protected $throughModel = _UNDEFINED;
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
    protected $throughModelClass = _UNDEFINED;

    /**
     * @var string|null
     */
    protected $thisTableRightKey = _UNDEFINED;
    /**
     * @var string|null
     */
    protected $throughTableLeftKey = _UNDEFINED;
    /**
     * @var string|null
     */
    protected $throughTableRightKey = _UNDEFINED;
    /**
     * @var string|null
     */
    protected $remoteTableLeftKey = _UNDEFINED;
}
