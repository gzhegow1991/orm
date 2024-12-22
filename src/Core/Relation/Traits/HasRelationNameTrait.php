<?php

namespace Gzhegow\Database\Core\Relation\Traits;

use Gzhegow\Database\Core\Orm;
use Illuminate\Database\Eloquent\Relations\Relation;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\Relations\RelationInterface;


/**
 * @mixin Relation
 * @mixin RelationInterface
 */
trait HasRelationNameTrait
{
    /**
     * @var string
     */
    protected $relationName;


    public function getRelationName() : string
    {
        return $this->relationName;
    }

    /**
     * @return static
     */
    public function setRelationName(?string $relationName) // : static
    {
        if ('' === $relationName) {
            throw new \LogicException(
                [ 'The `relationName` should be non-empty string' ]
            );
        }

        $relationPrefix = Orm::eloquentRelationPrefix();
        if ('' !== $relationPrefix) {
            if (0 !== strpos($relationName, $relationPrefix)) {
                throw new \LogicException(
                    [ 'The `relationName` should start with `relationPrefix`: ' . $relationName, $relationPrefix ]
                );
            }
        }

        $this->relationName = $relationName;

        return $this;
    }
}
