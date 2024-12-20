<?php

namespace Gzhegow\Database\Core\Relation\Spec;

use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelQueryBuilder;


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
    protected $relationName = _UNDEFINED;

    /**
     * @var EloquentModel
     */
    protected $thisModel = _UNDEFINED;
    /**
     * @var EloquentModel
     */
    protected $morphModel = _UNDEFINED;
    /**
     * @var EloquentModelQueryBuilder
     */
    protected $morphModelQuery = _UNDEFINED;

    /**
     * @var string|null
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
     * @var class-string<EloquentModel>
     */
    protected $morphClass = _UNDEFINED;

    /**
     * @var string|null
     */
    protected $remoteTableLeftKey = _UNDEFINED;
}
