<?php

namespace Gzhegow\Database\Core\Model\Traits;

use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;


/**
 * @mixin EloquentModel
 */
trait LoadTrait
{
    public function load($relations)
    {
        if (! $this->exists) {
            return $this;
        }

        return parent::load($relations);
    }

    public function loadMissing($relations)
    {
        if (! $this->exists) {
            return $this;
        }

        return parent::loadMissing($relations);
    }

    public function loadCount($relations)
    {
        if (! $this->exists) {
            return $this;
        }

        return parent::loadCount($relations);
    }

    public function loadMorph($relation, $relations)
    {
        if (! $this->exists) {
            return $this;
        }

        return parent::loadMorph($relation, $relations);
    }

    public function loadMorphCount($relation, $relations)
    {
        if (! $this->exists) {
            return $this;
        }

        return parent::loadMorphCount($relation, $relations);
    }
}
