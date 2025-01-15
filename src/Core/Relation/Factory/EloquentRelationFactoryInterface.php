<?php

namespace Gzhegow\Orm\Core\Relation\Factory;


use Gzhegow\Orm\Core\Relation\Spec\HasOneSpec;
use Gzhegow\Orm\Core\Relation\Spec\HasManySpec;
use Gzhegow\Orm\Core\Relation\Spec\MorphToSpec;
use Gzhegow\Orm\Core\Relation\Spec\MorphOneSpec;
use Gzhegow\Orm\Core\Relation\Spec\BelongsToSpec;
use Gzhegow\Orm\Core\Relation\Spec\MorphManySpec;
use Gzhegow\Orm\Core\Relation\Spec\MorphToManySpec;
use Gzhegow\Orm\Core\Relation\Spec\BelongsToManySpec;
use Gzhegow\Orm\Core\Relation\Spec\HasOneThroughSpec;
use Gzhegow\Orm\Core\Relation\Spec\HasManyThroughSpec;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Relations\HasOne;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Relations\HasMany;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Relations\MorphTo;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Relations\MorphOne;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Relations\BelongsTo;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Relations\MorphMany;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Relations\MorphToMany;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Relations\HasManyThrough;


/**
 * @mixin EloquentRelationFactory
 */
interface EloquentRelationFactoryInterface
{
    public function newBelongsTo(BelongsToSpec $spec) : BelongsTo;


    public function newHasOne(HasOneSpec $spec) : HasOne;

    public function newHasMany(HasManySpec $spec) : HasMany;


    public function newBelongsToMany(BelongsToManySpec $spec) : BelongsToMany;


    public function newHasOneThrough(HasOneThroughSpec $spec) : HasOneThrough;

    public function newHasManyThrough(HasManyThroughSpec $spec) : HasManyThrough;


    public function newMorphOne(MorphOneSpec $spec) : MorphOne;

    public function newMorphMany(MorphManySpec $spec) : MorphMany;


    public function newMorphTo(MorphToSpec $spec) : MorphTo;

    public function newMorphToMany(MorphToManySpec $spec) : MorphToMany;
}
