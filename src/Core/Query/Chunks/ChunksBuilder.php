<?php

namespace Gzhegow\Orm\Core\Query\Chunks;

use Gzhegow\Lib\Lib;
use Gzhegow\Orm\Core\Orm;
use Gzhegow\Orm\Exception\LogicException;
use Gzhegow\Orm\Exception\RuntimeException;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModel;
use Gzhegow\Orm\Package\Illuminate\Database\EloquentPdoQueryBuilder;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelQueryBuilder;


/**
 * @template-covariant T of EloquentModel
 */
class ChunksBuilder
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

    const MODE_SELECT_COUNT_NULL    = 'NULL';
    const MODE_SELECT_COUNT_NATIVE  = 'NATIVE';
    const MODE_SELECT_COUNT_EXPLAIN = 'EXPLAIN';

    const LIST_MODE_SELECT_COUNT = [
        self::MODE_SELECT_COUNT_NULL    => true,
        self::MODE_SELECT_COUNT_NATIVE  => true,
        self::MODE_SELECT_COUNT_EXPLAIN => true,
    ];

    const MODE_RESULT_CHUNK    = 'CHUNK';
    const MODE_RESULT_PAGINATE = 'PAGINATE';

    const LIST_MODE_RESULT = [
        self::MODE_RESULT_CHUNK    => true,
        self::MODE_RESULT_PAGINATE => true,
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
     * @var ChunksProcessor
     */
    protected $processor;


    /**
     * @var EloquentModelQueryBuilder<T>|null
     */
    protected $modelQuery = [];
    /**
     * @var EloquentPdoQueryBuilder|null
     */
    protected $pdoQuery = [];
    /**
     * @var EloquentModel|null
     */
    protected $model = [];
    /**
     * @var class-string<T>|null
     */
    protected $modelClass = [];

    /**
     * @see static::LIST_MODE_OFFSET
     *
     * @var string
     */
    protected $modeOffset = [];
    /**
     * @see static::LIST_MODE_FETCH
     *
     * @var string
     */
    protected $modeFetch = [];
    /**
     * @see static::LIST_MODE_SELECT_COUNT
     *
     * @var string
     */
    protected $modeSelectCount = [];
    /**
     * @see static::LIST_MODE_RESULT
     *
     * @var string
     */
    protected $modeResult = [];

    /**
     * @var int
     */
    protected $limitChunk = [];
    /**
     * @var int
     */
    private $limitChunkDefault = 20;

    /**
     * @var int|null
     */
    protected $limit = [];

    /**
     * @var int
     */
    protected $offset = [];
    /**
     * @var int
     */
    private $offsetDefault = 0;

    /**
     * @var string|null
     */
    protected $offsetColumn = [];
    /**
     * @var string
     */
    private $offsetColumnDefault = 'id';

    /**
     * @var string
     */
    protected $offsetOperator = [];
    /**
     * @var string
     */
    private $offsetOperatorDefault = self::OFFSET_OPERATOR_GT;

    /**
     * @var array{ 0?: mixed }
     */
    protected $offsetValue = [];

    /**
     * @var bool
     */
    protected $includeOffsetValue = [];
    /**
     * @var bool
     */
    private $includeOffsetValueDefault = true;

    /**
     * @var int
     */
    protected $perPage = [];
    /**
     * @var int
     */
    private $perPageDefault = 20;

    /**
     * @var int
     */
    protected $page = [];
    /**
     * @var int
     */
    protected $pagesDelta = [];

    /**
     * @var int|null
     */
    protected $totalItems = [];
    /**
     * @var int|null
     */
    private $totalDefault = null;
    /**
     * @var int|null
     */
    protected $totalPages = [];
    /**
     * @var int|null
     */
    private $pagesTotalDefault = null;


    private function __construct()
    {
        $this->modeSelectCount = static::MODE_SELECT_COUNT_NULL;

        $this->limitChunk = $this->limitChunkDefault;

        $this->offset = $this->offsetDefault;

        $this->offsetColumn = $this->offsetColumnDefault;
        $this->offsetOperator = $this->offsetOperatorDefault;
        $this->includeOffsetValue = $this->includeOffsetValueDefault;

        $this->perPage = $this->perPageDefault;

        $this->totalItems = $this->totalDefault;
        $this->totalPages = $this->pagesTotalDefault;

        $this->processor = Orm::newChunkProcessor();
    }


    /**
     * @return static
     */
    public static function from($from) // : static
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
    public static function tryFrom($from, \Throwable &$last = null) // : ?static
    {
        $last = null;

        Lib::php()->errors_start($b);

        $instance = null
            ?? static::tryFromInstance($from)
            ?? static::tryFromModelQuery($from)
            ?? static::tryFromModel($from)
            ?? static::tryFromModelClass($from)
            ?? static::tryFromPdoQuery($from);

        $errors = Lib::php()->errors_end($b);

        if (null === $instance) {
            foreach ( $errors as $error ) {
                $last = new LogicException($error, $last);
            }
        }

        return $instance;
    }


    /**
     * @return static|null
     */
    public static function tryFromInstance($from) // : ?static
    {
        if (! is_a($from, static::class)) {
            return Lib::php()->error(
                [ 'The `from` should be instance of: ' . static::class, $from ]
            );
        }

        return $from;
    }

    /**
     * @return static|null
     */
    public static function tryFromModelQuery($from) // : ?static
    {
        if (! is_a($from, EloquentModelQueryBuilder::class)) {
            return Lib::php()->error(
                [ 'The `from` should be instance of: ' . EloquentModelQueryBuilder::class, $from ]
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
    public static function tryFromPdoQuery($from) // : ?static
    {
        if (! is_a($from, EloquentPdoQueryBuilder::class)) {
            return Lib::php()->error(
                [ 'The `from` should be instance of: ' . EloquentPdoQueryBuilder::class, $from ]
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
    public static function tryFromModel($from) // : ?static
    {
        if (! is_a($from, EloquentModel::class)) {
            return Lib::php()->error(
                [ 'The `from` should be instance of: ' . EloquentModel::class, $from ]
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
    public static function tryFromModelClass($from) // : ?static
    {
        if (! is_subclass_of($from, EloquentModel::class)) {
            return Lib::php()->error(
                [ 'The `from` should be class-string of: ' . EloquentModel::class, $from ]
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


    protected function getProcessor() : ChunksProcessor
    {
        return $this->processor;
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
                    'The `modeOffset` should be one of: '
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
                    'The `modeFetch` should be one of: '
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
    public function setModeSelectCount(string $modeSelectCount) // : static
    {
        if (! isset(static::LIST_MODE_SELECT_COUNT[ $modeSelectCount ])) {
            throw new LogicException(
                [
                    'The `modeSelectCount` should be one of: '
                    . implode('|', array_keys(static::LIST_MODE_SELECT_COUNT)),
                    $modeSelectCount,
                ]
            );
        }

        $this->modeSelectCount = $modeSelectCount;

        return $this;
    }

    /**
     * @return static
     */
    public function setModeResult(string $modeResult) // : static
    {
        if (! isset(static::LIST_MODE_RESULT[ $modeResult ])) {
            throw new LogicException(
                [
                    'The `mode` should be one of: '
                    . implode('|', array_keys(static::LIST_MODE_RESULT)),
                    $modeResult,
                ]
            );
        }

        $this->modeResult = $modeResult;

        return $this;
    }


    public function getModeOffset() : string
    {
        return $this->modeOffset;
    }

    public function getModeFetch() : string
    {
        return $this->modeFetch;
    }

    public function getModeSelectCount() : string
    {
        return $this->modeSelectCount;
    }

    public function getModeResult() : string
    {
        return $this->modeResult;
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


    public function hasLimit() : ?int
    {
        return $this->limit;
    }

    public function getLimit() : int
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


    public function hasOffsetValue(&$result = null) : bool
    {
        $result = null;

        if (count($this->offsetValue)) {
            [ $result ] = $this->offsetValue;

            return true;
        }

        return false;
    }


    public function getOffset() : int
    {
        return $this->offset;
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
        $this->page = ($page > 0) ? $page : 1;

        return $this;
    }

    /**
     * @return static
     */
    public function setPagesDelta(?int $pagesDelta) // : static
    {
        $this->pagesDelta = ($pagesDelta > 0) ? $pagesDelta : 0;

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


    public function hasPagesDelta() : ?int
    {
        return $this->pagesDelta;
    }

    public function getPagesDelta() : int
    {
        return $this->pagesDelta;
    }


    /**
     * @return static
     */
    public function setTotalItems(?int $totalItems) // : static
    {
        $this->totalItems = ($totalItems > 0) ? $totalItems : null;

        return $this;
    }

    /**
     * @return static
     */
    public function setTotalPages(?int $totalPages) // : static
    {
        $this->totalPages = ($totalPages > 0) ? $totalPages : null;

        return $this;
    }


    public function hasTotalItems() : ?int
    {
        return $this->totalItems;
    }

    public function getTotalItems() : int
    {
        return $this->totalItems;
    }

    public function hasTotalPages() : ?int
    {
        return $this->totalPages;
    }

    public function getTotalPages() : int
    {
        return $this->totalPages;
    }


    /**
     * @return static
     */
    public function withFetchModel() // : static
    {
        $this->setModeFetch(static::MODE_FETCH_MODEL);

        return $this;
    }

    /**
     * @return static
     */
    public function withFetchPdo() // : static
    {
        $this->setModeFetch(static::MODE_FETCH_PDO);

        return $this;
    }


    /**
     * @return static
     */
    public function withOffsetNative(
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
    public function withOffsetAfter(
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
    public function withSelectCountNull() // : static
    {
        $this->setModeSelectCount(static::MODE_SELECT_COUNT_NULL);

        return $this;
    }

    /**
     * @return static
     */
    public function withSelectCountNative() // : static
    {
        $this->setModeSelectCount(static::MODE_SELECT_COUNT_NATIVE);

        return $this;
    }

    /**
     * @return static
     */
    public function withSelectCountExplain() // : static
    {
        $this->setModeSelectCount(static::MODE_SELECT_COUNT_EXPLAIN);

        return $this;
    }


    /**
     * @return static
     */
    public function withResultChunk(
        int $limitChunk = null,
        int $limit = null
    ) // : static
    {
        $this->setModeResult(static::MODE_RESULT_CHUNK);

        if (null !== $limitChunk) $this->setLimitChunk($limitChunk);

        $this->setLimit($limit);

        return $this;
    }

    /**
     * @return static
     */
    public function withResultPaginate(
        int $perPage = null,
        int $page = null,
        int $pagesDelta = null
    ) // : static
    {
        $this->setModeResult(static::MODE_RESULT_PAGINATE);

        if (null !== $perPage) $this->setPerPage($perPage);
        if (null !== $page) $this->setPage($page);

        $this->setPagesDelta($pagesDelta);

        return $this;
    }


    /**
     * @return static
     */
    public function chunksModelNativeForeach(
        int $limitChunk, int $limit = null,
        int $offset = null
    ) // : static
    {
        $this
            ->withFetchModel()
            ->withOffsetNative($offset)
            ->withResultChunk($limitChunk, $limit)
        ;

        return $this;
    }

    /**
     * @return static
     */
    public function chunksModelAfterForeach(
        int $limitChunk, int $limit = null,
        string $offsetColumn = null, string $offsetOperator = null, $offsetValue = null, bool $includeOffsetValue = null
    ) // : static
    {
        $this
            ->withFetchModel()
            ->withOffsetAfter($offsetColumn, $offsetOperator, $offsetValue, $includeOffsetValue)
            ->withResultChunk($limitChunk, $limit)
        ;

        return $this;
    }


    /**
     * @return static
     */
    public function chunksPdoNativeForeach(
        int $limitChunk, int $limit = null,
        int $offset = null
    ) // : static
    {
        $this
            ->withFetchPdo()
            ->withOffsetNative($offset)
            ->withResultChunk($limitChunk, $limit)
        ;

        return $this;
    }

    /**
     * @return static
     */
    public function chunksPdoAfterForeach(
        int $limitChunk, int $limit = null,
        string $offsetColumn = null, string $offsetOperator = null, $offsetValue = null, bool $includeOffsetValue = null
    ) // : static
    {
        $this
            ->withFetchPdo()
            ->withOffsetAfter($offsetColumn, $offsetOperator, $offsetValue, $includeOffsetValue)
            ->withResultChunk($limitChunk, $limit)
        ;

        return $this;
    }


    /**
     * @return static
     */
    public function paginateModelNativeForeach(
        int $perPage = null, int $page = null, int $pagesDelta = null,
        int $offset = null
    ) // : static
    {
        $this
            ->withFetchModel()
            ->withOffsetNative($offset)
            ->withResultPaginate($perPage, $page, $pagesDelta)
        ;

        return $this;
    }

    /**
     * @return static
     */
    public function paginateModelAfterForeach(
        int $perPage = null, int $page = null, int $pagesDelta = null,
        string $offsetColumn = null, string $offsetOperator = null, $offsetValue = null, bool $includeOffsetValue = null
    ) // : static
    {
        $this
            ->withFetchModel()
            ->withOffsetAfter($offsetColumn, $offsetOperator, $offsetValue, $includeOffsetValue)
            ->withResultPaginate($perPage, $page, $pagesDelta)
        ;

        return $this;
    }


    /**
     * @return static
     */
    public function paginatePdoNativeForeach(
        int $perPage = null, int $page = null, int $pagesDelta = null,
        int $offset = null
    ) // : static
    {
        $this
            ->withFetchPdo()
            ->withOffsetNative($offset)
            ->withResultPaginate($perPage, $page, $pagesDelta)
        ;

        return $this;
    }

    /**
     * @return static
     */
    public function paginatePdoAfterForeach(
        int $perPage = null, int $page = null, int $pagesDelta = null,
        string $offsetColumn = null, string $offsetOperator = null, $offsetValue = null, bool $includeOffsetValue = null
    ) // : static
    {
        $this
            ->withFetchPdo()
            ->withOffsetAfter($offsetColumn, $offsetOperator, $offsetValue, $includeOffsetValue)
            ->withResultPaginate($perPage, $page, $pagesDelta)
        ;

        return $this;
    }


    public function chunksForeach() : \Generator
    {
        /** @var array<string, array<string, array<string, callable>>> $map */

        $processor = $this->getProcessor();

        $map = [];
        $map[ static::MODE_RESULT_CHUNK ][ static::MODE_FETCH_MODEL ][ static::MODE_OFFSET_AFTER ] = [ $processor, 'chunksModelAfterForeach' ];
        $map[ static::MODE_RESULT_CHUNK ][ static::MODE_FETCH_MODEL ][ static::MODE_OFFSET_NATIVE ] = [ $processor, 'chunksModelNativeForeach' ];
        $map[ static::MODE_RESULT_CHUNK ][ static::MODE_FETCH_PDO ][ static::MODE_OFFSET_AFTER ] = [ $processor, 'chunksPdoAfterForeach' ];
        $map[ static::MODE_RESULT_CHUNK ][ static::MODE_FETCH_PDO ][ static::MODE_OFFSET_NATIVE ] = [ $processor, 'chunksPdoNativeForeach' ];

        $fn = $map[ $this->modeResult ][ $this->modeFetch ][ $this->modeOffset ];

        if (null === $fn) {
            throw new RuntimeException(
                [
                    'The `mode` is unknown',
                    $this->modeResult,
                    $this->modeFetch,
                    $this->modeOffset,
                ]
            );
        }

        $generator = call_user_func($fn, $this);

        return $generator;
    }


    public function paginateResult() : ChunksPaginateResult
    {
        /** @var array<string, array<string, array<string, callable>>> $map */

        $processor = $this->getProcessor();

        $map = [];
        $map[ static::MODE_RESULT_PAGINATE ][ static::MODE_FETCH_MODEL ][ static::MODE_OFFSET_AFTER ] = [ $processor, 'paginateModelAfterForeach' ];
        $map[ static::MODE_RESULT_PAGINATE ][ static::MODE_FETCH_MODEL ][ static::MODE_OFFSET_NATIVE ] = [ $processor, 'paginateModelNativeForeach' ];
        $map[ static::MODE_RESULT_PAGINATE ][ static::MODE_FETCH_PDO ][ static::MODE_OFFSET_AFTER ] = [ $processor, 'paginatePdoAfterForeach' ];
        $map[ static::MODE_RESULT_PAGINATE ][ static::MODE_FETCH_PDO ][ static::MODE_OFFSET_NATIVE ] = [ $processor, 'paginatePdoNativeForeach' ];

        $fn = $map[ $this->modeResult ][ $this->modeFetch ][ $this->modeOffset ];

        if (null === $fn) {
            throw new RuntimeException(
                [
                    'The `mode` is unknown',
                    $this->modeResult,
                    $this->modeFetch,
                    $this->modeOffset,
                ]
            );
        }

        $generator = call_user_func($fn, $this);

        return $generator;
    }
}
