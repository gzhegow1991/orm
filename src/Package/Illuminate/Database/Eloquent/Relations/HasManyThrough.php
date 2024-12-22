<?php

namespace Gzhegow\Database\Package\Illuminate\Database\Eloquent\Relations;

use Gzhegow\Database\Core\Relation\Traits\HasRelationNameTrait;
use Illuminate\Database\Eloquent\Relations\HasManyThrough as HasManyThroughBase;


class HasManyThrough extends HasManyThroughBase implements
    RelationInterface
{
    use HasRelationNameTrait;


    public function addConstraints()
    {
        /** @see parent::addConstraints() */

        $localValue = $this->farParent->getAttribute($this->localKey);

        $this->performJoin();

        if (static::$constraints) {
            $this->query->where(
                $this->getQualifiedFirstKeyName(),
                '=',
                $localValue
            );
        }
    }
}
