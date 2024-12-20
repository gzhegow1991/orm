<?php

namespace Gzhegow\Database\Core\Query\Builder;

use Gzhegow\Lib\Lib;
use Gzhegow\Database\Exception\LogicException;
use Gzhegow\Database\Exception\RuntimeException;
use Illuminate\Support\Collection as EloquentSupportCollection;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;
use Gzhegow\Database\Package\Illuminate\Database\EloquentPdoQueryBuilder;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelQueryBuilder;


/**
 * @template-covariant T of EloquentModel
 */
class ChunkBuilder
{
    const MODE_OFFSET_AFTER  = 'AFTER';
    const MODE_OFFSET_NATIVE = 'NATIVE';

    const LIST_MODE_OFFSET = [
        self::MODE_OFFSET_AFTER  => true,
        self::MODE_OFFSET_NATIVE => true,
    ];

    const MODE_FETCH_MODEL = 'MODEL';
    const MODE_FETCH_PDO   = 'PDO';

    const LIST_MODE_FETCH = [
        self::MODE_FETCH_MODEL => true,
        self::MODE_FETCH_PDO   => true,
    ];

    const MODE_YIELD_CHUNK = 'CHUNK';
    const MODE_YIELD_PAGE  = 'PAGE';

    const LIST_MODE_YIELD = [
        self::MODE_YIELD_CHUNK => true,
        self::MODE_YIELD_PAGE  => true,
    ];

    const OFFSET_OPERATOR_GT  = '>';
    const OFFSET_OPERATOR_GTE = '>=';
    const OFFSET_OPERATOR_LT  = '<';
    const OFFSET_OPERATOR_LTE = '<=';

    const LIST_OFFSET_OPERATOR = [
        self::OFFSET_OPERATOR_GT  => true,
        self::OFFSET_OPERATOR_GTE => true,
        self::OFFSET_OPERATOR_LT  => true,
        self::OFFSET_OPERATOR_LTE => true,
    ];


    /**
     * @var EloquentModelQueryBuilder<T>|null
     */
    protected $modelQuery;
    /**
     * @var EloquentPdoQueryBuilder|null
     */
    protected $pdoQuery;
    /**
     * @var EloquentModel|null
     */
    protected $model;
    /**
     * @var class-string<T>|null
     */
    protected $modelClass;

    /**
     * @see static::LIST_MODE_OFFSET
     *
     * @var string
     */
    protected $modeOffset;
    /**
     * @see static::LIST_MODE_FETCH
     *
     * @var string
     */
    protected $modeFetch;
    /**
     * @see static::LIST_MODE_YIELD
     *
     * @var string
     */
    protected $modeYield;

    /**
     * @var int
     */
    protected $limitChunk;
    /**
     * @var int
     */
    protected $limitChunkDefault = 20;

    /**
     * @var int|null
     */
    protected $limit;

    /**
     * @var int
     */
    protected $offset = 0;
    /**
     * @var array{ 0?: mixed }
     */
    protected $offsetValue;
    /**
     * @var bool
     */
    protected $includeOffsetValue = true;

    /**
     * @var string|null
     */
    protected $offsetColumn;
    /**
     * @var string
     */
    protected $offsetColumnDefault = 'id';

    /**
     * @var string
     */
    protected $offsetOperator = self::OFFSET_OPERATOR_GT;

    /**
     * @var int
     */
    protected $perPage;
    /**
     * @var int
     */
    protected $perPageDefault = 20;

    /**
     * @var int
     */
    protected $page = 1;
    /**
     * @var int
     */
    protected $pagesDelta = 1;

    /**
     * @var int
     */
    protected $pagesTotal;


    private function __construct()
    {
        $this->limitChunk = $this->limitChunkDefault;

        $this->offsetColumn = $this->offsetColumnDefault;

        $this->perPage = $this->perPageDefault;
    }


    /**
     * @return static
     */
    public static function from($from) : self
    {
        $instance = static::tryFrom($from, $error);

        if (null === $instance) {
            throw $error;
        }

        return $instance;
    }

    /**
     * @return static|null
     */
    public static function tryFrom($from, \Throwable &$last = null) : ?self
    {
        $last = null;

        Lib::php_errors_start($b);

        $instance = null
            ?? static::tryFromInstance($from)
            ?? static::tryFromModelQuery($from)
            ?? static::tryFromModel($from)
            ?? static::tryFromModelClass($from)
            ?? static::tryFromPdoQuery($from);

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
    protected static function tryFromInstance($from) : ?self
    {
        if (! is_a($from, static::class)) {
            return Lib::php_error(
                [
                    'The `from` should be instance of: ' . static::class,
                    $from,
                ]
            );
        }

        return $from;
    }

    /**
     * @return static|null
     */
    protected static function tryFromModelQuery($from) : ?self
    {
        if (! is_a($from, EloquentModelQueryBuilder::class)) {
            return Lib::php_error(
                [
                    'The `from` should be instance of: ' . EloquentModelQueryBuilder::class,
                    $from,
                ]
            );
        }

        $modelQuery = $from;
        $pdoQuery = $from->getQuery();
        $model = $from->getModel();
        $modelClass = get_class($model);

        $instance = new static();
        $instance->modelQuery = $modelQuery;
        $instance->pdoQuery = $pdoQuery;
        $instance->model = $model;
        $instance->modelClass = $modelClass;

        $instance->offsetColumnDefault = $model->getKeyName();

        return $instance;
    }

    /**
     * @return static|null
     */
    protected static function tryFromPdoQuery($from) : ?self
    {
        if (! is_a($from, EloquentPdoQueryBuilder::class)) {
            return Lib::php_error(
                [
                    'The `from` should be instance of: ' . EloquentPdoQueryBuilder::class,
                    $from,
                ]
            );
        }

        $pdoQuery = $from;

        $instance = new static();
        $instance->pdoQuery = $pdoQuery;

        return $instance;
    }

    /**
     * @return static|null
     */
    protected static function tryFromModel($from) : ?self
    {
        if (! is_a($from, EloquentModel::class)) {
            return Lib::php_error(
                [
                    'The `from` should be instance of: ' . EloquentModel::class,
                    $from,
                ]
            );
        }

        $model = $from;
        $modelClass = get_class($from);
        $modelQuery = $from->newQuery();
        $pdoQuery = $modelQuery->getQuery();

        $instance = new static();
        $instance->model = $model;
        $instance->modelClass = $modelClass;
        $instance->modelQuery = $modelQuery;
        $instance->pdoQuery = $pdoQuery;

        $instance->offsetColumnDefault = $model->getKeyName();

        return $instance;
    }

    /**
     * @return static|null
     */
    protected static function tryFromModelClass($from) : ?self
    {
        if (! is_subclass_of($from, EloquentModel::class)) {
            return Lib::php_error(
                [
                    'The `from` should be class-string of: ' . EloquentModel::class,
                    $from,
                ]
            );
        }

        $modelClass = $from;
        $model = new $from();
        $modelQuery = $model->newQuery();
        $pdoQuery = $modelQuery->getQuery();

        $instance = new static();
        $instance->modelClass = $modelClass;
        $instance->model = $model;
        $instance->modelQuery = $modelQuery;
        $instance->pdoQuery = $pdoQuery;

        $instance->offsetColumnDefault = $model->getKeyName();

        return $instance;
    }


    public function getModelQuery() : EloquentModelQueryBuilder
    {
        return $this->modelQuery;
    }

    public function getPdoQuery() : EloquentPdoQueryBuilder
    {
        return $this->pdoQuery;
    }

    public function getModel() : EloquentModel
    {
        return $this->model;
    }

    public function getModelClass() : string
    {
        return $this->modelClass;
    }


    /**
     * @return static
     */
    public function setModeOffset(string $modeOffset) // : static
    {
        if (! isset(static::LIST_MODE_OFFSET[ $modeOffset ])) {
            throw new LogicException(
                [
                    'The `mode` should be one of: '
                    . implode('|', array_keys(static::LIST_MODE_OFFSET)),
                    $modeOffset,
                ]
            );
        }

        $this->modeOffset = $modeOffset;

        return $this;
    }

    /**
     * @return static
     */
    public function setModeFetch(string $modeFetch) // : static
    {
        if (! isset(static::LIST_MODE_FETCH[ $modeFetch ])) {
            throw new LogicException(
                [
                    'The `mode` should be one of: '
                    . implode('|', array_keys(static::LIST_MODE_FETCH)),
                    $modeFetch,
                ]
            );
        }

        $this->modeFetch = $modeFetch;

        return $this;
    }

    /**
     * @return static
     */
    public function setModeYield(string $modeYield) // : static
    {
        if (! isset(static::LIST_MODE_YIELD[ $modeYield ])) {
            throw new LogicException(
                [
                    'The `mode` should be one of: '
                    . implode('|', array_keys(static::LIST_MODE_YIELD)),
                    $modeYield,
                ]
            );
        }

        $this->modeYield = $modeYield;

        return $this;
    }


    /**
     * @return static
     */
    public function setPerPage(int $perPage) // : static
    {
        $this->perPage = ($perPage > 0) ? $perPage : $this->perPageDefault;

        return $this;
    }

    /**
     * @return static
     */
    public function setPage(int $page) // : static
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @return static
     */
    public function setPagesDelta(?int $pagesDelta) // : static
    {
        $this->pagesDelta = ($pagesDelta > 0) ? $pagesDelta : 1;

        return $this;
    }

    /**
     * @return static
     */
    public function setPagesTotal(int $pagesTotal) // : static
    {
        $this->pagesTotal = $pagesTotal;

        return $this;
    }


    public function getPerPage() : int
    {
        return $this->perPage;
    }

    public function getPage() : int
    {
        return $this->page;
    }

    public function getPagesDelta() : ?int
    {
        return $this->pagesDelta;
    }

    public function getPagesTotal() : ?int
    {
        return $this->pagesTotal;
    }


    /**
     * @return static
     */
    public function setLimitChunk(int $limitChunk) // : static
    {
        $this->limitChunk = ($limitChunk > 0) ? $limitChunk : $this->limitChunkDefault;

        return $this;
    }

    /**
     * @return static
     */
    public function setLimit(?int $limit) // : static
    {
        $this->limit = ($limit > 0) ? $limit : null;

        return $this;
    }


    public function getLimitChunk() : int
    {
        return $this->limitChunk;
    }

    public function getLimit() : ?int
    {
        return $this->limit;
    }


    /**
     * @return static
     */
    public function setOffset(int $offset) // : static
    {
        $this->offset = ($offset > 0) ? $offset : 0;

        return $this;
    }

    /**
     * @return static
     */
    public function setOffsetValue(array $offsetValue = []) // : static
    {
        $this->offsetValue = count($offsetValue)
            ? $offsetValue
            : [];

        return $this;
    }

    /**
     * @return static
     */
    public function setIncludeOffsetValue(bool $includeOffsetValue) // : static
    {
        $this->includeOffsetValue = $includeOffsetValue;

        return $this;
    }


    public function getOffset() : int
    {
        return $this->offset;
    }


    public function hasOffsetValue(&$result = null) : bool
    {
        $result = null;

        if (count($this->offsetValue)) {
            [ $result ] = $this->offsetValue;

            return true;
        }

        return false;
    }

    public function getOffsetValue() // : mixed
    {
        if (! $this->hasOffsetValue($result)) {
            throw new RuntimeException(
                'The `offsetValue` should be not empty'
            );
        }

        return $result;
    }


    public function getIncludeOffsetValue() : bool
    {
        return $this->includeOffsetValue;
    }


    /**
     * @return static
     */
    public function setOffsetColumn(string $offsetColumn) // : static
    {
        if ('' === $offsetColumn) {
            throw new LogicException(
                [
                    'The `offsetColumn` should be non-empty string',
                ]
            );
        }

        $this->offsetColumn = $offsetColumn;

        return $this;
    }

    /**
     * @return static
     */
    public function setOffsetOperator(string $offsetOperator) // : static
    {
        if (! isset(static::LIST_OFFSET_OPERATOR[ $offsetOperator ])) {
            throw new LogicException(
                [
                    'The `offsetOperator` should be one of: '
                    . implode('|', array_keys(static::LIST_OFFSET_OPERATOR)),
                ]
            );
        }

        $this->offsetOperator = $offsetOperator;

        return $this;
    }


    public function getOffsetColumn() : string
    {
        return $this->offsetColumn;
    }

    public function getOffsetOperator() : string
    {
        return $this->offsetOperator;
    }


    /**
     * @return static
     */
    public function fetchModel() // : static
    {
        $this->setModeFetch(static::MODE_FETCH_MODEL);

        return $this;
    }

    /**
     * @return static
     */
    public function fetchPdo() // : static
    {
        $this->setModeFetch(static::MODE_FETCH_PDO);

        return $this;
    }


    /**
     * @return static
     */
    public function offsetNative(
        int $offset = null
    ) // : static
    {
        $this->setModeOffset(static::MODE_OFFSET_NATIVE);

        if (null !== $offset) $this->setOffset($offset);

        return $this;
    }

    /**
     * @return static
     */
    public function offsetAfter(
        string $offsetColumn = null,
        string $offsetOperator = null,
        $offsetValue = null,
        bool $includeOffsetValue = null
    ) // : static
    {
        $this->setModeOffset(static::MODE_OFFSET_AFTER);

        if (null !== $offsetColumn) $this->setOffsetColumn($offsetColumn);
        if (null !== $offsetOperator) $this->setOffsetOperator($offsetOperator);
        if (null !== $offsetValue) $this->setOffsetValue([ $offsetValue ]);
        if (null !== $includeOffsetValue) $this->setIncludeOffsetValue($includeOffsetValue);

        return $this;
    }


    /**
     * @return static
     */
    public function yieldChunk(
        int $limitChunk = null,
        int $limit = null
    ) // : static
    {
        $this->setModeYield(static::MODE_YIELD_CHUNK);

        if (null !== $limitChunk) $this->setLimitChunk($limitChunk);
        if (null !== $limit) $this->setLimit($limit);

        return $this;
    }

    /**
     * @return static
     */
    public function yieldPage(
        int $perPage = null,
        int $page = null,
        int $pagesDelta = null
    ) // : static
    {
        $this->setModeYield(static::MODE_YIELD_PAGE);

        if (null !== $perPage) $this->setPerPage($perPage);
        if (null !== $page) $this->setPage($page);
        if (null !== $pagesDelta) $this->setPagesDelta($pagesDelta);

        return $this;
    }


    public function foreach() : \Generator
    {
        /** @var array<string, array<string, array<string, callable>>> $map */

        $map = [];
        $map[ static::MODE_YIELD_CHUNK ][ static::MODE_FETCH_MODEL ][ static::MODE_OFFSET_AFTER ] = [ $this, 'doChunkModelAfterForeach' ];
        $map[ static::MODE_YIELD_CHUNK ][ static::MODE_FETCH_MODEL ][ static::MODE_OFFSET_NATIVE ] = [ $this, 'doChunkModelNativeForeach' ];
        $map[ static::MODE_YIELD_CHUNK ][ static::MODE_FETCH_PDO ][ static::MODE_OFFSET_AFTER ] = [ $this, 'doChunkPdoAfterForeach' ];
        $map[ static::MODE_YIELD_CHUNK ][ static::MODE_FETCH_PDO ][ static::MODE_OFFSET_NATIVE ] = [ $this, 'doChunkPdoNativeForeach' ];
        $map[ static::MODE_YIELD_PAGE ][ static::MODE_FETCH_MODEL ][ static::MODE_OFFSET_AFTER ] = [ $this, 'doPageModelAfterForeach' ];
        $map[ static::MODE_YIELD_PAGE ][ static::MODE_FETCH_MODEL ][ static::MODE_OFFSET_NATIVE ] = [ $this, 'doPageModelNativeForeach' ];
        $map[ static::MODE_YIELD_PAGE ][ static::MODE_FETCH_PDO ][ static::MODE_OFFSET_AFTER ] = [ $this, 'doPagePdoAfterForeach' ];
        $map[ static::MODE_YIELD_PAGE ][ static::MODE_FETCH_PDO ][ static::MODE_OFFSET_NATIVE ] = [ $this, 'doPagePdoNativeForeach' ];

        $fn = $map[ $this->modeYield ][ $this->modeFetch ][ $this->modeOffset ];

        if (null === $fn) {
            throw new RuntimeException(
                [
                    'The `mode` is unknown',
                    $this->modeYield,
                    $this->modeFetch,
                    $this->modeOffset,
                ]
            );
        }

        $generator = call_user_func($fn);

        return $generator;
    }


    /**
     * @return \Generator<EloquentModelCollection<T>|T[]>
     */
    protected function doChunkModelNativeForeach() : \Generator
    {
        if (static::MODE_YIELD_CHUNK !== $this->modeYield) throw new RuntimeException([ 'The `modeYield` should be: ' . static::MODE_YIELD_CHUNK, $this->modeYield ]);
        if (static::MODE_FETCH_MODEL !== $this->modeFetch) throw new RuntimeException([ 'The `modeFetch` should be: ' . static::MODE_FETCH_MODEL, $this->modeFetch ]);
        if (static::MODE_OFFSET_NATIVE !== $this->modeOffset) throw new RuntimeException([ 'The `modeOffset` should be: ' . static::MODE_OFFSET_NATIVE, $this->modeOffset ]);

        $modelQuery = $this->getModelQuery();

        $limitChunk = $this->getLimitChunk();

        $limit = $this->getLimit();
        $offset = $this->getOffset();

        $total = $limit ?? INF;
        $left = $total;

        $isFirst = true;
        do {
            $queryClone = clone $modelQuery;

            $limitChunkCurrent = min($left, $limitChunk);
            if (! $limitChunkCurrent) {
                break;
            }

            if ($isFirst && ($offset > 0)) {
                $queryClone->offset(
                    $offset
                );
            }

            $queryClone->limit(
                $limitChunkCurrent
            );

            $models = $queryClone->get();
            $modelsCount = $models->count();

            if (! $modelsCount) {
                break;
            }

            yield $models;

            if ($modelsCount < $limitChunkCurrent) {
                break;
            }

            $left = max(0, $left - $modelsCount);

            if ($isFirst) {
                $isFirst = false;
            }
        } while ( $left && ($modelsCount === $limitChunkCurrent) );
    }

    /**
     * @return \Generator<EloquentSupportCollection<\stdClass>|\stdClass[]>
     */
    protected function doChunkPdoNativeForeach() : \Generator
    {
        if (static::MODE_YIELD_CHUNK !== $this->modeYield) throw new RuntimeException([ 'The `modeYield` should be: ' . static::MODE_YIELD_CHUNK, $this->modeYield ]);
        if (static::MODE_FETCH_PDO !== $this->modeFetch) throw new RuntimeException([ 'The `modeFetch` should be: ' . static::MODE_FETCH_PDO, $this->modeFetch ]);
        if (static::MODE_OFFSET_NATIVE !== $this->modeOffset) throw new RuntimeException([ 'The `modeOffset` should be: ' . static::MODE_OFFSET_NATIVE, $this->modeOffset ]);

        $pdoQuery = $this->getPdoQuery();

        $limitChunk = $this->getLimitChunk();

        $limit = $this->getLimit();
        $offset = $this->getOffset();

        $total = $limit ?? INF;
        $left = $total;

        $isFirst = true;
        do {
            $queryClone = clone $pdoQuery;

            $limitChunkCurrent = min($left, $limitChunk);
            if (! $limitChunkCurrent) {
                break;
            }

            if ($isFirst && ($offset > 0)) {
                $queryClone->offset(
                    $offset
                );
            }

            $queryClone->limit(
                $limitChunkCurrent
            );

            $rows = $queryClone->get();
            $rowsCount = $rows->count();

            if (! $rowsCount) {
                break;
            }

            yield $rows;

            if ($rowsCount < $limitChunkCurrent) {
                break;
            }

            $left = max(0, $left - $rowsCount);

            if ($isFirst) {
                $isFirst = false;
            }
        } while ( $left && ($rowsCount === $limitChunkCurrent) );
    }


    /**
     * @return \Generator<EloquentModelCollection<T>|T[]>
     */
    protected function doChunkModelAfterForeach() : \Generator
    {
        if (static::MODE_YIELD_CHUNK !== $this->modeYield) throw new RuntimeException([ 'The `modeYield` should be: ' . static::MODE_YIELD_CHUNK, $this->modeYield ]);
        if (static::MODE_FETCH_MODEL !== $this->modeFetch) throw new RuntimeException([ 'The `modeFetch` should be: ' . static::MODE_FETCH_MODEL, $this->modeFetch ]);
        if (static::MODE_OFFSET_AFTER !== $this->modeOffset) throw new RuntimeException([ 'The `modeOffset` should be: ' . static::MODE_OFFSET_AFTER, $this->modeOffset ]);

        $modelQuery = $this->getModelQuery();
        $pdoQuery = $this->getPdoQuery();

        $limitChunk = $this->getLimitChunk();

        $limit = $this->getLimit();
        $offset = $this->getOffset();

        $offsetColumn = $this->getOffsetColumn();
        $offsetOperator = $this->getOffsetOperator();
        $offsetValueStart = $this->getOffsetValue();
        $includeOffsetValueStart = $this->getIncludeOffsetValue();

        if ($pdoQuery->orders) {
            throw new LogicException(
                [
                    'The `query` MUST NOT have `orders`, or use `->chunkNativeForeach()`',
                    $pdoQuery,
                ]
            );
        }

        if ($pdoQuery->limit) {
            throw new LogicException(
                [
                    'The `query` MUST NOT have `limit`, use function arguments',
                    $pdoQuery,
                ]
            );
        }

        if ($pdoQuery->offset) {
            throw new LogicException(
                [
                    'The `query` MUST NOT have `offset`, use function arguments',
                    $pdoQuery,
                ]
            );
        }

        if ($pdoQuery->columns
            && ! in_array($offsetColumn, $pdoQuery->columns)
        ) {
            throw new LogicException(
                [
                    "You probably forget to add `offsetColumn` to select in your query: {$offsetColumn}",
                    $pdoQuery,
                ]
            );
        }

        $total = $limit ?? INF;
        $left = $total;

        $offsetOperatorFirst = $offsetOperator;
        if ($includeOffsetValueStart) {
            if ($offsetOperator === static::OFFSET_OPERATOR_GT) {
                $offsetOperatorFirst = static::OFFSET_OPERATOR_GTE;
            }
            if ($offsetOperator === static::OFFSET_OPERATOR_LT) {
                $offsetOperatorFirst = static::OFFSET_OPERATOR_LTE;
            }
        }

        $offsetOrder = 'asc';
        if (false
            || ($offsetOperator === static::OFFSET_OPERATOR_LT)
            || ($offsetOperator === static::OFFSET_OPERATOR_LTE)
        ) {
            $offsetOrder = 'desc';
        }

        $offsetValueCurrent = $offsetValueStart ?? null;

        $queryClone = clone $modelQuery;
        $queryClone = EloquentModelQueryBuilder::groupWheres($queryClone);
        $queryClone->orderBy(
            $offsetColumn,
            $offsetOrder
        );

        $isFirst = true;
        do {
            $queryCloneCurrent = clone $queryClone;

            $limitChunkCurrent = min($left, $limitChunk);
            if (! $limitChunkCurrent) {
                break;
            }

            $queryCloneCurrent->limit(
                $limitChunkCurrent
            );

            if ($offsetValueCurrent) {
                $offsetOperatorCurrent = $isFirst
                    ? $offsetOperatorFirst
                    : $offsetOperator;

                $queryCloneCurrent->where(
                    $offsetColumn,
                    $offsetOperatorCurrent,
                    $offsetValueCurrent
                );
            }

            if ($isFirst && ($offset > 0)) {
                $queryCloneCurrent->offset(
                    $offset
                );
            }

            $models = $queryCloneCurrent->get();
            $modelsCount = $models->count();

            if (! $modelsCount) {
                break;
            }

            yield $models;

            if ($modelsCount < $limitChunkCurrent) {
                break;
            }

            $offsetValueCurrent = $models->last()->{$offsetColumn};

            $left = max(0, $left - $modelsCount);

            if ($isFirst) {
                $isFirst = false;
            }
        } while ( $left && ($modelsCount === $limitChunkCurrent) );
    }

    /**
     * @return \Generator<EloquentSupportCollection<\stdClass>|\stdClass[]>
     */
    protected function doChunkPdoAfterForeach() : \Generator
    {
        if (static::MODE_YIELD_CHUNK !== $this->modeYield) throw new RuntimeException([ 'The `modeYield` should be: ' . static::MODE_YIELD_CHUNK, $this->modeYield ]);
        if (static::MODE_FETCH_PDO !== $this->modeFetch) throw new RuntimeException([ 'The `modeFetch` should be: ' . static::MODE_FETCH_PDO, $this->modeFetch ]);
        if (static::MODE_OFFSET_AFTER !== $this->modeOffset) throw new RuntimeException([ 'The `modeOffset` should be: ' . static::MODE_OFFSET_AFTER, $this->modeOffset ]);

        $pdoQuery = $this->getPdoQuery();

        $limitChunk = $this->getLimitChunk();

        $limit = $this->getLimit();
        $offset = $this->getOffset();

        $offsetColumn = $this->getOffsetColumn();
        $offsetOperator = $this->getOffsetOperator();
        $offsetValueStart = $this->getOffsetValue();
        $includeOffsetValueStart = $this->getIncludeOffsetValue();

        if ($pdoQuery->orders) {
            throw new LogicException(
                [
                    'The `query` MUST NOT have `orders`, or use `->chunkNativeForeach()`',
                    $pdoQuery,
                ]
            );
        }

        if ($pdoQuery->limit) {
            throw new LogicException(
                [
                    'The `query` MUST NOT have `limit`, use function arguments',
                    $pdoQuery,
                ]
            );
        }

        if ($pdoQuery->offset) {
            throw new LogicException(
                [
                    'The `query` MUST NOT have `offset`, use function arguments',
                    $pdoQuery,
                ]
            );
        }

        if ($pdoQuery->columns
            && ! in_array($offsetColumn, $pdoQuery->columns)
        ) {
            throw new LogicException(
                [
                    "You probably forget to add `offsetColumn` to select in your query: {$offsetColumn}",
                    $pdoQuery,
                ]
            );
        }

        $total = $limit ?? INF;
        $left = $total;

        $offsetOperatorFirst = $offsetOperator;
        if ($includeOffsetValueStart) {
            if ($offsetOperator === static::OFFSET_OPERATOR_GT) {
                $offsetOperatorFirst = static::OFFSET_OPERATOR_GTE;
            }
            if ($offsetOperator === static::OFFSET_OPERATOR_LT) {
                $offsetOperatorFirst = static::OFFSET_OPERATOR_LTE;
            }
        }

        $offsetOrder = 'asc';
        if (false
            || ($offsetOperator === static::OFFSET_OPERATOR_LT)
            || ($offsetOperator === static::OFFSET_OPERATOR_LTE)
        ) {
            $offsetOrder = 'desc';
        }

        $offsetValueCurrent = $offsetValueStart ?? null;

        $pdoQueryClone = clone $pdoQuery;
        $pdoQueryClone = EloquentPdoQueryBuilder::groupWheres($pdoQueryClone);
        $pdoQueryClone->orderBy(
            $offsetColumn,
            $offsetOrder
        );

        $isFirst = true;
        do {
            $pdoQueryCloneCurrent = clone $pdoQueryClone;

            $limitChunkCurrent = min($left, $limitChunk);
            if (! $limitChunkCurrent) {
                break;
            }

            $pdoQueryCloneCurrent->limit(
                $limitChunkCurrent
            );

            if ($offsetValueCurrent) {
                $offsetOperatorCurrent = $isFirst
                    ? $offsetOperatorFirst
                    : $offsetOperator;

                $pdoQueryCloneCurrent->where(
                    $offsetColumn,
                    $offsetOperatorCurrent,
                    $offsetValueCurrent
                );
            }

            if ($isFirst && ($offset > 0)) {
                $pdoQueryCloneCurrent->offset(
                    $offset
                );
            }

            $rows = $pdoQueryCloneCurrent->get();
            $rowsCount = $rows->count();

            if (! $rowsCount) {
                break;
            }

            yield $rows;

            if ($rowsCount < $limitChunkCurrent) {
                break;
            }

            $offsetValueCurrent = $rows->last()->{$offsetColumn};

            $left = max(0, $left - $rowsCount);

            if ($isFirst) {
                $isFirst = false;
            }
        } while ( $left && ($rowsCount === $limitChunkCurrent) );
    }


    /**
     * @return array
     */
    protected function doPageModelNativeForeach() : array
    {
        if (static::MODE_YIELD_PAGE !== $this->modeYield) throw new RuntimeException([ 'The `modeYield` should be: ' . static::MODE_YIELD_PAGE, $this->modeYield ]);
        if (static::MODE_FETCH_MODEL !== $this->modeFetch) throw new RuntimeException([ 'The `modeFetch` should be: ' . static::MODE_FETCH_MODEL, $this->modeFetch ]);
        if (static::MODE_OFFSET_NATIVE !== $this->modeOffset) throw new RuntimeException([ 'The `modeOffset` should be: ' . static::MODE_OFFSET_NATIVE, $this->modeOffset ]);

        $this->pageCalculateLimits();

        $generator = $this->doChunkModelNativeForeach();

        return $generator;
    }

    /**
     * @return array
     */
    protected function doPagePdoNativeForeach() : array
    {
        if (static::MODE_YIELD_PAGE !== $this->modeYield) throw new RuntimeException([ 'The `modeYield` should be: ' . static::MODE_YIELD_PAGE, $this->modeYield ]);
        if (static::MODE_FETCH_PDO !== $this->modeFetch) throw new RuntimeException([ 'The `modeFetch` should be: ' . static::MODE_FETCH_PDO, $this->modeFetch ]);
        if (static::MODE_OFFSET_NATIVE !== $this->modeOffset) throw new RuntimeException([ 'The `modeOffset` should be: ' . static::MODE_OFFSET_NATIVE, $this->modeOffset ]);

        $this->pageCalculateLimits();

        $generator = $this->doChunkPdoNativeForeach();

        return $result;
    }


    /**
     * @return array
     */
    protected function doPageModelAfterForeach() : array
    {
        if (static::MODE_YIELD_PAGE !== $this->modeYield) throw new RuntimeException([ 'The `modeYield` should be: ' . static::MODE_YIELD_PAGE, $this->modeYield ]);
        if (static::MODE_FETCH_MODEL !== $this->modeFetch) throw new RuntimeException([ 'The `modeFetch` should be: ' . static::MODE_FETCH_MODEL, $this->modeFetch ]);
        if (static::MODE_OFFSET_AFTER !== $this->modeOffset) throw new RuntimeException([ 'The `modeOffset` should be: ' . static::MODE_OFFSET_AFTER, $this->modeOffset ]);

        $this->pageCalculateLimits();

        $generator = $this->doChunkModelAfterForeach();

        return $generator;
    }

    /**
     * @return array
     */
    protected function doPagePdoAfterForeach() : array
    {
        if (static::MODE_YIELD_PAGE !== $this->modeYield) throw new RuntimeException([ 'The `modeYield` should be: ' . static::MODE_YIELD_PAGE, $this->modeYield ]);
        if (static::MODE_FETCH_PDO !== $this->modeFetch) throw new RuntimeException([ 'The `modeFetch` should be: ' . static::MODE_FETCH_PDO, $this->modeFetch ]);
        if (static::MODE_OFFSET_AFTER !== $this->modeOffset) throw new RuntimeException([ 'The `modeOffset` should be: ' . static::MODE_OFFSET_AFTER, $this->modeOffset ]);

        $this->pageCalculateLimits();

        $generator = $this->doChunkPdoAfterForeach();

        // foreach ( $generator as $i => $chunk ) {
        //     $stat = [
        //         'items' => $chunk,
        //         'pages' => [
        //             $this->page => count($chunk),
        //         ],
        //     ];
        //
        //     yield $i => $stat;
        // }

        return $generator;
    }


    protected function pageCalculateLimits() : void
    {
        $perPage = $this->getPerPage();
        $page = $this->getPage();
        $pagesDelta = $this->getPagesDelta();

        $limitChunk = $perPage;
        $limit = ($pagesDelta !== -1) ? ($pagesDelta * $perPage) : null;
        $offset = $page ? (($page - 1) * $perPage) : null;

        $this->setLimitChunk($limitChunk);
        $this->setLimit($limit);
        $this->setOffset($offset);
    }
}
