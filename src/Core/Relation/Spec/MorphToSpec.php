<?php

namespace Gzhegow\Orm\Core\Relation\Spec;

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
    protected $relationName = [];

    /**
     * @var EloquentModel
     */
    protected $thisModel = [];
    /**
     * @var EloquentModel
     */
    protected $morphModel = [];
    /**
     * @var EloquentModelQueryBuilder
     */
    protected $morphModelQuery = [];

    /**
     * @var string|null
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
     * @var class-string<EloquentModel>
     */
    protected $morphClass = [];

    /**
     * @var string|null
     */
    protected $remoteTableLeftKey = [];
}
