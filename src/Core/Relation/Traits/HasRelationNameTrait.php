<?php

namespace Gzhegow\Database\Core\Relation\Traits;

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
        if (null !== $relationName) {
            if ('' === $relationName) {
                throw new \LogicException(
                    [
                        'The `relationName` should be non-empty string',
                    ]
                );
            }

            if ('_' !== $relationName[ 0 ]) {
                throw new \LogicException(
                    [
                        'The `relationName` should begin with `_` symbol',
                        $relationName,
                    ]
                );
            }
        }

        $this->relationName = $relationName;

        return $this;
    }
}
