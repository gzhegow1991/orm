<?php

namespace Gzhegow\Orm\Package\Illuminate\Database\Eloquent;

use Gzhegow\Orm\Exception\LogicException;
use Gzhegow\Orm\Exception\RuntimeException;
use Gzhegow\Orm\Exception\Runtime\DeprecatedException;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Base\EloquentModel;
use Illuminate\Database\Eloquent\Collection as EloquentModelCollectionBase;


/**
 * @template-covariant T of EloquentModel
 */
class EloquentModelCollection extends EloquentModelCollectionBase
{
    /**
     * @var class-string<T>
     */
    protected $modelClass;


    public function __toString() : string
    {
        // > originally, casting model/collection to string returns JSON, guess to posibility to store whole model to one DB cell
        // > it is deprecated magic that forces any string casting or dumping to do useless job
        throw new DeprecatedException('Casting collection to string is deprecated');
    }


    /**
     * @param T|class-string<T> $modelOrClass
     *
     * @return static
     */
    public function setModelClass($modelOrClass)
    {
        /** @var class-string<T> $modelClass */

        $modelClass = is_object($modelOrClass)
            ? get_class($modelOrClass)
            : $modelOrClass;

        if (! is_subclass_of($modelOrClass, EloquentModel::class)) {
            throw new LogicException(
                [
                    'The `modelOrClass` should be instance of class-string of: ' . EloquentModel::class,
                    $modelOrClass,
                ]
            );
        }

        $this->modelClass = $modelClass;

        return $this;
    }

    /**
     * @return class-string<T>
     */
    public function getModelClass() : string
    {
        return $this->modelClass;
    }


    public function load($relations)
    {
        if (! $this->items) {
            return $this;
        }

        $this->assertLoadAllowed(__FUNCTION__);

        return parent::load($relations);
    }

    public function loadMissing($relations)
    {
        if (! $this->items) {
            return $this;
        }

        $this->assertLoadAllowed(__FUNCTION__);

        return parent::loadMissing($relations);
    }

    public function loadCount($relations)
    {
        if (! $this->items) {
            return $this;
        }

        $this->assertLoadAllowed(__FUNCTION__);

        return parent::loadCount($relations);
    }

    public function loadMorph($relation, $relations)
    {
        if (! $this->items) {
            return $this;
        }

        $this->assertLoadAllowed(__FUNCTION__);

        return parent::loadMorph($relation, $relations);
    }

    public function loadMorphCount($relation, $relations)
    {
        if (! $this->items) {
            return $this;
        }

        $this->assertLoadAllowed(__FUNCTION__);

        return parent::loadMorphCount($relation, $relations);
    }


    protected function assertLoadAllowed(?string $function = null) : void
    {
        $function = $function ?? __FUNCTION__;

        foreach ( $this->items as $item ) {
            if (! is_a($item, EloquentModel::class)) {
                throw new RuntimeException(
                    "Unable to call {$function}() due to collection contains non-models"
                );
            }

            if (! $item->exists) {
                throw new RuntimeException(
                    "Unable to call {$function}() due to collection contains models that is not exists in DB"
                );
            }
        }
    }
}
