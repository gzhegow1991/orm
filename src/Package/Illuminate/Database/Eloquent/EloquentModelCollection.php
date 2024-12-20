<?php

namespace Gzhegow\Database\Package\Illuminate\Database\Eloquent;

use Gzhegow\Lib\Lib;
use Gzhegow\Database\Exception\LogicException;
use Gzhegow\Database\Exception\RuntimeException;
use Illuminate\Database\Eloquent\Collection as EloquentModelCollectionBase;


/**
 * @template-covariant T of EloquentModel
 */
class EloquentModelCollection extends EloquentModelCollectionBase
{
    // >>> state
    /** > gzhegow, коллекция создана, чтобы выполнить INSERT в рамках текущего скрипта */
    public $recentlyCreated = false;

    /**
     * @var class-string<T>
     */
    protected $modelClass;


    /**
     * @return static
     */
    public static function from($from, array $options = []) : self
    {
        /** @see parent::__construct() */
        /** @see parent::make() */

        $instance = static::tryFrom($from, $options, $error);

        if (null === $instance) {
            throw $error;
        }

        return $instance;
    }

    /**
     * @return static|null
     */
    public static function tryFrom($from, array $options = [], \Throwable &$last = null) : ?self
    {
        /** @see parent::__construct() */
        /** @see parent::make() */

        $last = null;

        Lib::php_errors_start($b);

        $instance = null
            ?? static::tryFromInstance($from, $options)
            ?? static::tryFromModel($from, $options)
            ?? static::tryFromArray($from, $options);

        $errors = Lib::php_errors_end($b);

        if (null === $instance) {
            foreach ( $errors as $error ) {
                $last = new LogicException($error, null, $last);
            }
        }

        $isRecentlyCreated = $options[ 'recentlyCreated' ] ?? null;

        $instance->recentlyCreated = (bool) $isRecentlyCreated;

        return $instance;
    }


    /**
     * @return static|null
     */
    protected static function tryFromInstance($from, array $options = []) : ?self
    {
        if (! is_a($from, static::class)) {
            return Lib::php_error(
                [ 'The `from` should be instance of: ' . static::class, $from ]
            );
        }

        $instance = clone $from;

        return $instance;
    }

    /**
     * @return static|null
     */
    protected static function tryFromModel($from, array $options = []) : ?self
    {
        if (! is_a($from, EloquentModel::class)) {
            return Lib::php_error(
                [ 'The `from` should be instance of: ' . static::class, $from ]
            );
        }

        $instance = $from->newCollection([ $from ]);

        return $instance;
    }

    /**
     * @return static|null
     */
    protected static function tryFromArray($from, array $options = []) : ?self
    {
        if (! is_array($from)) {
            return Lib::php_error(
                [ 'The `from` should be array of single class models', $from ]
            );
        }

        $result = new static($from);

        return $result;
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


    protected function assertLoadAllowed(string $function = null) : void
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
