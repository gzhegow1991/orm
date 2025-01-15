<?php

namespace Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Relations;

use Gzhegow\Orm\Core\Relation\Traits\HasRelationNameTrait;
use Illuminate\Database\Eloquent\Relations\HasOneThrough as HasOneThroughBase;


class HasOneThrough extends HasOneThroughBase implements
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
