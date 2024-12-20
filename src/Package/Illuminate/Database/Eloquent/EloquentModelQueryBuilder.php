<?php

namespace Gzhegow\Database\Package\Illuminate\Database\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Gzhegow\Database\Exception\LogicException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Gzhegow\Database\Core\Query\ModelQuery\Traits\ChunkTrait;
use Illuminate\Database\Query\Builder as EloquentPdoQueryBuilder;
use Gzhegow\Database\Core\Query\ModelQuery\Traits\PersistenceTrait;
use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilderBase;
use Gzhegow\Database\Exception\Exception\Resource\ResourceNotFoundException;


/**
 * @template-covariant T of EloquentModel
 */
class EloquentModelQueryBuilder extends EloquentQueryBuilderBase
{
    use ChunkTrait;
    use PersistenceTrait;


    /**
     * @var T
     */
    protected $model;


    /**
     * @param EloquentPdoQueryBuilder $query
     * @param T                       $model
     */
    public function __construct(
        EloquentPdoQueryBuilder $query,
        //
        EloquentModel $model
    )
    {
        parent::__construct($query);

        $this->setModel($model);
    }


    /**
     * @return T
     */
    public function getModel()
    {
        $model = parent::getModel();

        return $model;
    }

    /**
     * @param T $model
     *
     * @return static
     */
    public function setModel(Model $model)
    {
        $this->doSetModel($model);

        return $this;
    }

    protected function doSetModel(EloquentModel $model)
    {
        parent::setModel($model);

        return $this;
    }


    /**
     * @return T
     */
    public function newModelInstance($attributes = [])
    {
        /** @see parent::newModelInstance() */

        $connection = $this->query->getConnection();
        $connectionName = $connection->getName();

        $instance = $this->model->newInstance($attributes);

        $instance->setConnection($connectionName);

        return $instance;
    }


    /**
     * @var static
     */
    protected $wheresGroupStack = [];

    /**
     * @return static
     */
    public function wheresGroup() // : static
    {
        $this->wheresGroupStack[] = $this->wheres;

        $this->wheres = [];

        return $this;
    }

    /**
     * @return static
     */
    public function endWheresGroup() // : static
    {
        if (! count($this->wheresGroupStack)) {
            throw new LogicException(
                'The `whereGroupWhereStack` is empty'
            );
        }

        $wheresLast = array_pop($this->wheresGroupStack);

        static::groupWheres($this);

        $queryPdo = $this->getQuery();

        $queryPdo->wheres = array_merge(
            $wheresLast,
            $queryPdo->wheres
        );

        return $this;
    }

    public static function groupWheres(EloquentModelQueryBuilder $query) : EloquentModelQueryBuilder
    {
        $queryPdo = $query->getQuery();

        $wheresCurrent = $queryPdo->wheres;

        $queryPdo->wheres = [];
        $queryPdo->where(
            static function (EloquentPdoQueryBuilder $queryPdoWhere) use ($wheresCurrent) {
                $queryPdoWhere->wheres = $wheresCurrent;
            }
        );

        return $query;
    }


    /**
     * @return EloquentModelCollection<T>|T[]
     */
    public function get($columns = [ '*' ])
    {
        $model = $this->getModel();
        $modelKey = $model->getKeyName();

        $queryClone = $this->applyScopes();
        $pdoQueryClone = $queryClone->getQuery();

        if ($pdoQueryClone->columns && ! $pdoQueryClone->orders) {
            // > gzhegow, MariaDB
            // > отдает всегда одно и то же, т.е. сортирует под капотом
            // > но всегда разное в зависимости от числа полей в SELECT
            // > select * from `w3j_user` limit 1; // = {id: 1}
            // > select `id` from `w3j_user` limit 1; // = {id: 6}
            // > select `id`, `uuid` from `w3j_user` limit 1; // = {id: 3}

            // > gzhegow, MariaDB & PostgreSQL
            $pdoQueryClone->orderBy($modelKey, 'ASC');
        }

        $models = $queryClone->getModels($columns);

        if (count($models)) {
            $models = $queryClone->eagerLoadRelations($models);
        }

        $collection = $model->newCollection($models);

        return $collection;
    }


    /**
     * @return T|null
     */
    public function first($columns = [ '*' ])
    {
        $model = static::first($columns);

        return $model;
    }

    /**
     * @return T
     * @throws ResourceNotFoundException
     */
    public function firstOrFail($columns = [ '*' ])
    {
        $model = static::first($columns);

        if (null === $model) {
            throw new ResourceNotFoundException(
                [
                    'Resource not found',
                    get_class($this->model),
                    $this,
                ]
            );
        }

        return $model;
    }


    /**
     * @return bool
     */
    public function exists()
    {
        $status = parent::exists();

        return $status;
    }

    /**
     * @return bool
     * @throws ResourceNotFoundException
     */
    public function existsOrFail()
    {
        $status = static::exists();

        if (null === $status) {
            throw new ResourceNotFoundException(
                [
                    'Resource not found',
                    get_class($this->model),
                    $this,
                ]
            );
        }

        return $status;
    }


    /**
     * todo-gzhegow, доделать, сейчас работает так же как из коробки Laravel, нужно поддержку "только айди"
     *
     * > gzhegow, по прежнему считаю, что экономия на выбираемых в SELECT полях - глупость
     * > но коль скоро тему подняли, вот, подготавливаю к тому, чтобы принудительно запрашивать только ID
     * > остальные поля задавать руками. Посмотри на doctrine. `оно` тянет целые агрегаты залпом - десятки таблиц
     * > и ничего, считается быстрым. Стоит разделять запросы на `тяжелые` и `по айди` и вот в тяжелых писать RAW SQL,
     * > а не издеваться над мозгами, ссылаясь на вероятный хайлоад. Кол-во запросов и отсутствие индексов будет лаг,
     * > а если база висит всё равно - это должно быть масштабирование на несколько машин, а не выборкой "только айди"...
     */
    protected function parseWithRelations(array $relations)
    {
        /** @see parent::parseWithRelations() */

        $fnNull = static function () { };

        $fnConstraints = static function ($columns, $fn) {
            return static function ($query) use ($columns, $fn) {
                $columns = array_filter($columns);

                if ($columns) {
                    foreach ( $columns as $i => $column ) {
                        if ($column === '@id') {
                            unset($columns[ $i ]);

                            if ($query instanceof Relation) {
                                foreach ( $query->getBaseQuery()->wheres as $where ) {
                                    $columns[] = $where[ 'column' ];
                                }
                            }
                        }
                    }

                    if ($query instanceof BelongsToMany) {
                        $relatedTable = $query->getRelated()->getTable();

                        foreach ( $columns as $i => $column ) {
                            if (false !== strpos($column, '.')) {
                                continue;
                            }

                            $columns[ $i ] = "{$relatedTable}.{$column}";
                        }
                    }

                    $query->select($columns);
                }

                if ($fn) {
                    $fn($query);
                }
            };
        };

        $results = [];

        foreach ( $relations as $name => $constraints ) {
            if (is_int($name)) {
                $name = $constraints;
                $constraints = $fnNull;
            }

            if (false !== strpos($name, '.')) {
                $path = explode('.', $name);

                while ( $path ) {
                    $last = implode('.', $path);

                    if (! isset($last)) {
                        $results[ $last ] = $fnNull;
                    }

                    array_shift($path);
                }
            }

            $results[ $name ] = $constraints;
        }

        foreach ( $results as $name => $constraints ) {
            if (false !== strpos($name, ':')) {
                unset($results[ $name ]);

                [ $name, $columns ] = explode(':', $name);

                $columns = explode(',', $columns);

                $constraints = $fnConstraints($columns, $constraints);

                $results[ $name ] = $constraints;
            }
        }

        return $results;
    }
}
