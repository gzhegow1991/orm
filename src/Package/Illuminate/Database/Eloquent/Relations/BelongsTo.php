<?php

namespace Gzhegow\Database\Package\Illuminate\Database\Eloquent\Relations;

use Illuminate\Database\Eloquent\Model;
use Gzhegow\Database\Core\Relation\Traits\HasRelationNameTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo as BaseBelongsTo;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;


class BelongsTo extends BaseBelongsTo implements
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

        $child = $this->doAssociate($model);

        return $child;
    }

    protected function doAssociate(?EloquentModel $model) : EloquentModel
    {
        /** @var EloquentModel $child */

        $child = $this->child;

        if ($model) {
            $model->hasRawAttribute($this->ownerKey, $modelId);

            $child->setRawAttribute($this->foreignKey, $modelId ?? $model);
            $child->setRelation($this->relationName, $model);

        } else {
            $child->unsetRelation($this->relationName);
        }

        return $child;
    }


    /**
     * @return Model
     */
    public function dissociate()
    {
        /** @see parent::dissociate() */

        $this->child->setAttribute($this->foreignKey, null);

        $this->child->setRelation($this->relationName, null);

        return $this->child;
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
