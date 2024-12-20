<?php

namespace Gzhegow\Database\Core\Model\Traits;

use Gzhegow\Lib\Lib;
use Gzhegow\Database\Core\Orm;
use Illuminate\Database\Eloquent\Model;
use Gzhegow\Database\Exception\LogicException;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;
use Gzhegow\Database\Package\Illuminate\Database\EloquentPdoQueryBuilder;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelQueryBuilder;


/**
 * @mixin EloquentModel
 */
trait FactoryTrait
{
    /**
     * @return static
     */
    public static function new(array $attributes = [], \Closure $fn = null)
    {
        $instance = new static($attributes);
        $instance->recentlyCreated = true;

        if (null !== $fn) {
            $fn->call($instance, $instance);
        }

        return $instance;
    }


    /**
     * @return static
     */
    public static function from($from, array $options = []) : self
    {
        /** @see Model::__construct() */
        /** @see Model::newInstance() */
        /** @see Model::newFromBuilder() */

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
        $last = null;

        Lib::php_errors_start($b);

        $instance = null
            ?? static::tryFromInstance($from, $options)
            ?? static::tryFromArray($from, $options)
            ?? static::tryFromStdClass($from, $options);

        $errors = Lib::php_errors_end($b);

        if (null === $instance) {
            foreach ( $errors as $error ) {
                $last = new LogicException($error, null, $last);
            }
        }

        return $instance;
    }


    /**
     * @return static|null
     */
    public static function tryFromInstance($from, array $options = []) : ?self
    {
        if (! is_a($from, static::class)) {
            return Lib::php_error(
                [ 'The `from` should be instance of: ' . static::class, $from ]
            );
        }

        $instance = $from::new();

        return $instance;
    }

    /**
     * @return static|null
     */
    public static function tryFromArray($from, array $options = []) : ?self
    {
        if (! is_array($from)) {
            return Lib::php_error([ 'The `from` should be array', $from ]);
        }

        $instance = static::new($from);

        return $instance;
    }

    /**
     * @return static|null
     */
    public static function tryFromStdClass($from, array $options = []) : ?self
    {
        if (! is_a($from, \stdClass::class)) {
            return Lib::php_error([ 'The `from` should be \stdClass', $from ]);
        }

        $instance = static::new((array) $from);

        return $instance;
    }


    /**
     * @return static
     */
    public function newInstance($attributes = [], $exists = false)
    {
        /** @see Model::newInstance() */

        $attributes = $attributes ?? [];
        $exists = (bool) ($exists ?? false);

        if (! (false
            || is_array($attributes)
            || is_a($attributes, \stdClass::class)
        )) {
            throw new LogicException(
                [ 'The `attributes` should be array or \stdClass', $attributes ]
            );
        }

        $instance = new static((array) $attributes);
        $instance->exists = $exists;

        $instance->setConnection($this->getConnectionName());

        return $instance;
    }


    public function newInstanceByBuilder(array $attributes = [], string $connection = null)
    {
        $attributes = $attributes ?? [];

        $instance = new static();
        $instance->exists = true;

        $instance->setRawAttributes($attributes, true);

        $instance->setConnection($connection ?? $this->getConnectionName());

        $instance->fireModelEvent('retrieved', false);

        return $instance;
    }


    /**
     * @param class-string<EloquentModel> $modelClass
     *
     * @return static
     */
    public function newModelWithSameConnection(string $modelClass, array $attributes = [], bool $exists = false)
    {
        if (! is_subclass_of($modelClass, EloquentModel::class)) {
            throw new LogicException(
                [ 'The `class` should be class-string of: ' . EloquentModel::class, $modelClass ]
            );
        }

        $instance = new $modelClass($attributes);
        $instance->exists = $exists;

        $instance->setConnection($this->getConnectionName());

        return $instance;
    }


    /**
     * @return static
     *
     * @deprecated
     * @internal
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
        /** @see Model::newFromBuilder() */

        $attributes = $attributes ?? [];

        if (! (false
            || is_array($attributes)
            || is_a($attributes, \stdClass::class)
        )) {
            throw new LogicException(
                [ 'The `attributes` should be array or \stdClass', $attributes ]
            );
        }

        $instance = $this->newInstanceByBuilder((array) $attributes, $connection);

        return $instance;
    }

    /**
     * @return static
     *
     * @deprecated
     * @internal
     */
    protected function newRelatedInstance($class)
    {
        /** @see HasRelationships::newRelatedInstance() */

        $instance = $this->newModelWithSameConnection($class);

        return $instance;
    }


    /**
     * @return EloquentModelCollection<static>
     */
    public function newCollection(array $models = []) : EloquentModelCollection
    {
        /** @see Model::newCollection() */

        $collection = Orm::newEloquentModelCollection(
            $models
        );

        return $collection;
    }


    public function newPdoQueryBuilder() : EloquentPdoQueryBuilder
    {
        $connectionInstance = $this->getConnection();

        $pdoQuery = Orm::newEloquentPdoQueryBuilder(
            $connectionInstance,
            $connectionInstance->getQueryGrammar(),
            $connectionInstance->getPostProcessor()
        );

        return $pdoQuery;
    }

    /**
     * @return EloquentPdoQueryBuilder
     *
     * @deprecated
     * @internal
     */
    protected function newBaseQueryBuilder()
    {
        /** @see Model::newBaseQueryBuilder() */

        $pdoQuery = $this->newPdoQueryBuilder();

        return $pdoQuery;
    }


    /**
     * @return EloquentModelQueryBuilder<static>
     */
    public function newModelQueryBuilder(EloquentPdoQueryBuilder $query) : EloquentModelQueryBuilder
    {
        $modelQuery = Orm::newEloquentModelQueryBuilder(
            $query,
            $this
        );

        return $modelQuery;
    }

    /**
     * @param EloquentPdoQueryBuilder $query
     *
     * @return EloquentModelQueryBuilder<static>
     *
     * @deprecated
     * @internal
     */
    public function newEloquentBuilder($query)
    {
        /** @see Model::newEloquentBuilder() */

        $modelQuery = $this->newModelQueryBuilder($query);

        return $modelQuery;
    }


    /**
     * @return EloquentModelQueryBuilder<static>
     */
    public function newModelQuery()
    {
        /** @see Model::newModelQuery() */

        $pdoQuery = $this->newPdoQueryBuilder();

        $modelQuery = $this->newModelQueryBuilder($pdoQuery);

        $modelQuery->setModel($this);

        return $modelQuery;
    }


    /**
     * @return EloquentModelQueryBuilder<static>
     */
    public function newQuery()
    {
        /** @see Model::newQuery() */

        $modelQuery = $this->newModelQuery();

        $this->registerGlobalScopes($modelQuery);

        $modelQuery->with($this->with);
        $modelQuery->withCount($this->withCount);

        return $modelQuery;
    }


    /**
     * @return EloquentModelQueryBuilder<static>
     *
     * @deprecated
     * @internal
     */
    public function newQueryWithoutScope($scope)
    {
        /** @see Model::newQueryWithoutScope() */

        $modelQuery = $this->newQuery();

        $modelQuery->withoutGlobalScope($scope);

        return $modelQuery;
    }

    /**
     * @return EloquentModelQueryBuilder<static>
     *
     * @deprecated
     * @internal
     */
    public function newQueryWithoutScopes()
    {
        /** @see Model::newQueryWithoutScopes() */

        $modelQuery = $this->newModelQuery();

        $modelQuery->with($this->with);
        $modelQuery->withCount($this->withCount);

        return $modelQuery;
    }

    /**
     * @return EloquentModelQueryBuilder<static>
     *
     * @deprecated
     * @internal
     */
    public function newQueryWithoutRelationships()
    {
        /** @see Model::newQueryWithoutRelationships() */

        $modelQuery = $this->newModelQuery();

        $this->registerGlobalScopes($modelQuery);

        return $modelQuery;
    }


    /**
     * @return EloquentModelQueryBuilder<static>
     *
     * @deprecated
     * @internal
     */
    public function newQueryForRestoration($ids)
    {
        /** @see Model::newQueryForRestoration() */

        $modelQuery = $this->newModelQuery();

        $modelQuery->with($this->with);
        $modelQuery->withCount($this->withCount);

        if (is_array($ids)) {
            $modelQuery->whereIn($this->getQualifiedKeyName(), $ids);

        } else {
            $modelQuery->whereKey($ids);
        }

        return $modelQuery;
    }
}
