<?php

namespace Gzhegow\Database\Package\Illuminate\Database\Eloquent\Relations;

use Illuminate\Database\Eloquent\Model;
use Gzhegow\Database\Core\Relation\Traits\HasRelationNameTrait;
use Illuminate\Database\Eloquent\Relations\MorphTo as MorphToBase;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;


class MorphTo extends MorphToBase implements
    RelationInterface
{
    use HasRelationNameTrait;


    /**
     * @param EloquentModel $model
     *
     * @return EloquentModel
     */
    public function associate($model)
    {
        /** @see parent::associate() */

        $parent = $this->doAssociate($model);

        return $parent;
    }

    protected function doAssociate(?EloquentModel $model) : EloquentModel
    {
        /** @var EloquentModel $parent */

        $parent = $this->parent;

        if ($model) {
            $modelMorphClass = $model->getMorphClass();

            $model->hasRawAttribute($this->ownerKey, $modelId);

            $parent->setRawAttribute($this->foreignKey, $modelId ?? $model);
            $parent->setRawAttribute($this->morphType, $modelMorphClass);
            $parent->setRelation($this->relationName, $model);

        } else {
            $parent->unsetRelation($this->relationName);
        }

        return $parent;
    }


    /**
     * @return Model
     */
    public function dissociate()
    {
        /** @see parent::dissociate() */

        $parent = $this->parent;

        $parent->setAttribute($this->foreignKey, null);

        $parent->setAttribute($this->morphType, null);

        $parent->setRelation($this->relationName, null);

        return $parent;
    }



    public function addConstraints()
    {
        /** @see parent::addConstraints() */

        if (static::$constraints) {
            $table = $this->related->getTable();

            $this->query->where(
                $table . '.' . $this->ownerKey,
                '=',
                $this->child->getAttribute($this->foreignKey)
            );
        }
    }
}
