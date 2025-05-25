<?php

namespace Gzhegow\Orm\Core\Model\Traits;

use Gzhegow\Lib\Lib;
use Gzhegow\Orm\Core\Orm;
use Illuminate\Database\Eloquent\Model;
use Gzhegow\Orm\Exception\LogicException;
use Gzhegow\Orm\Exception\RuntimeException;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Base\EloquentModel;
use Gzhegow\Orm\Package\Illuminate\Database\EloquentPdoQueryBuilder;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelCollection;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelQueryBuilder;


/**
 * @mixin EloquentModel
 */
trait FactoryTrait
{
    /**
     * @return static
     *
     * @deprecated
     * @internal
     */
    public function newInstance($attributes = [], $exists = false)
    {
        /** @see Model::newInstance() */

        $attributes = Lib::php()->to_array($attributes);
        $exists = boolval($exists ?? false);

        $instance = $this->newInstanceWithState(
            $attributes,
            [
                'connection' => $this->getConnectionName(),
                'exists'     => $exists,
            ]
        );

        return $instance;
    }

    /**
     * @return static
     */
    public function newInstanceWithState(array $attributes = [], array $state = [])
    {
        $instance = new static($attributes);

        $state[ 'connection' ] = $state[ 'connection' ] ?? $this->getConnectionName();

        foreach ( $state as $key => $value ) {
            if (! property_exists($this, $key)) {
                throw new RuntimeException(
                    [ 'Missing property: ' . $key, $this, $state ]
                );
            }

            $instance->{$key} = $value;
        }

        return $instance;
    }

    /**
     * @return static
     */
    public function newInstanceWithSetState(array $attributes = [], ?\Closure $fnSetState = null)
    {
        $instance = new static($attributes);

        if (null !== $fnSetState) {
            $fnSetState->call($instance, $instance);
        }

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

        $instance = $this->newModelWithState(
            $class,
            [],
            [
                'connection' => $this->getConnectionName(),
            ]
        );

        return $instance;
    }

    /**
     * @param class-string<EloquentModel> $modelClass
     *
     * @return static
     */
    public function newModelWithState(
        string $modelClass,
        array $attributes = [], array $state = []
    )
    {
        if (! is_subclass_of($modelClass, EloquentModel::class)) {
            throw new LogicException(
                [ 'The `class` should be class-string of: ' . EloquentModel::class, $modelClass ]
            );
        }

        $instance = new $modelClass($attributes);

        foreach ( $state as $key => $value ) {
            if (! property_exists($this, $key)) {
                throw new RuntimeException(
                    [ 'Missing property: ' . $key, $this, $state ]
                );
            }

            $instance->{$key} = $value;
        }

        return $instance;
    }

    /**
     * @param class-string<EloquentModel> $modelClass
     *
     * @return static
     */
    public function newModelWithSetState(
        string $modelClass,
        array $attributes = [], ?\Closure $fnSetState = null
    )
    {
        if (! is_subclass_of($modelClass, EloquentModel::class)) {
            throw new LogicException(
                [ 'The `class` should be class-string of: ' . EloquentModel::class, $modelClass ]
            );
        }

        $instance = new $modelClass($attributes);

        if (null !== $fnSetState) {
            $fnSetState->call($instance, $instance);
        }

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

        $_attributes = Lib::php()->to_array($attributes);

        $instance = $this->newInstanceWithState(
            [],
            [
                //
                'connection'         => $connection ?? $this->getConnectionName(),
                'exists'             => true,
                //
                // > sync()
                'attributeCastCache' => [],
                'attributes'         => $_attributes,
                'classCastCache'     => [],
                'original'           => $_attributes,
            ]
        );

        $instance->fireModelEvent('retrieved', false);

        return $instance;
    }


    /**
     * @return EloquentModelCollection<static>|static[]
     */
    public function newCollection(array $models = []) : EloquentModelCollection
    {
        /** @see Model::newCollection() */

        $collection = Orm::newEloquentModelCollection($models);
        $collection->setModelClass($this);

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
    public function newBaseQueryBuilder()
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
