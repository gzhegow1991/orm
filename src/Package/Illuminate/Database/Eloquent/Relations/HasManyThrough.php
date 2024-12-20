<?php

namespace Gzhegow\Database\Package\Illuminate\Database\Eloquent\Relations;

use Gzhegow\Database\Core\Relation\Traits\HasRelationNameTrait;
use Illuminate\Database\Eloquent\Relations\HasManyThrough as BaseHasManyThrough;


class HasManyThrough extends BaseHasManyThrough implements
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
