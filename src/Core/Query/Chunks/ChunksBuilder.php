<?php

namespace Gzhegow\Orm\Core\Query\Chunks;

use Gzhegow\Orm\Core\Orm;
use Gzhegow\Orm\Exception\LogicException;
use Gzhegow\Lib\Modules\Php\Result\Result;
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
     * @var null
     */
    private $limitDefault = null;

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
    private $pageDefault = 1;

    /**
     * @var int
     */
    protected $pagesDelta = [];
    /**
     * @var int
     */
    private $pagesDeltaDefault = 0;

    /**
     * @var int|null
     */
    protected $totalItems = [];
    /**
     * @var int|null
     */
    private $totalItemsDefault = null;

    /**
     * @var int|null
     */
    protected $totalPages = [];
    /**
     * @var int|null
     */
    private $totalPagesDefault = null;


    private function __construct()
    {
        $this->modeSelectCount = static::MODE_SELECT_COUNT_NULL;

        $this->limitChunk = $this->limitChunkDefault;
        $this->limit = $this->limitDefault;

        $this->offset = $this->offsetDefault;

        $this->offsetColumn = $this->offsetColumnDefault;
        $this->offsetOperator = $this->offsetOperatorDefault;
        $this->includeOffsetValue = $this->includeOffsetValueDefault;

        $this->perPage = $this->perPageDefault;
        $this->page = $this->pageDefault;
        $this->pagesDelta = $this->pagesDeltaDefault;

        $this->totalItems = $this->totalItemsDefault;
        $this->totalPages = $this->totalPagesDefault;

        $this->processor = Orm::newChunkProcessor();
    }


    /**
     * @return static|bool|null
     */
    public static function fromStatic($from, $ctx = null)
    {
        if ($from instanceof static) {
            return Result::ok($ctx, $from);
        }

        return Result::err(
            $ctx,
            [ 'The `from` should be instance of: ' . static::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return static|bool|null
     */
    public static function fromModelQuery($from, $ctx = null)
    {
        if (! ($from instanceof EloquentModelQueryBuilder)) {
            return Result::err(
                $ctx,
                [ 'The `from` should be instance of: ' . EloquentModelQueryBuilder::class, $from ],
                [ __FILE__, __LINE__ ]
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

        return Result::ok($ctx, $instance);
    }

    /**
     * @return static|bool|null
     */
    public static function fromPdoQuery($from, $ctx = null)
    {
        if (! ($from instanceof EloquentPdoQueryBuilder)) {
            return Result::err(
                $ctx,
                [ 'The `from` should be instance of: ' . EloquentPdoQueryBuilder::class, $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $pdoQuery = $from;

        $instance = new static();
        $instance->pdoQuery = $pdoQuery;

        return Result::ok($ctx, $instance);
    }

    /**
     * @return static|bool|null
     */
    public static function fromModel($from, $ctx = null)
    {
        if (! ($from instanceof EloquentModel)) {
            return Result::err(
                $ctx,
                [ 'The `from` should be instance of: ' . EloquentModel::class, $from ],
                [ __FILE__, __LINE__ ]
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

        return Result::ok($ctx, $instance);
    }

    /**
     * @return static|bool|null
     */
    public static function fromModelClass($from, $ctx = null)
    {
        if (! (is_string($from) && ('' !== $from))) {
            return Result::err(
                $ctx,
                [ 'The `from` should be non-empty string', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! is_subclass_of($from, EloquentModel::class)) {
            return Result::err(
                $ctx,
                [ 'The `from` should be class-string of: ' . EloquentModel::class, $from ],
                [ __FILE__, __LINE__ ]
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

        return Result::ok($ctx, $instance);
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
    public function setModeOffset(?string $modeOffset)
    {
        if (null !== $modeOffset) {
            if (! isset(static::LIST_MODE_OFFSET[ $modeOffset ])) {
                throw new LogicException(
                    [
                        'The `modeOffset` should be one of: '
                        . implode('|', array_keys(static::LIST_MODE_OFFSET)),
                        $modeOffset,
                    ]
                );
            }
        }

        $this->modeOffset = $modeOffset ?? [];

        return $this;
    }

    /**
     * @return static
     */
    public function setModeFetch(?string $modeFetch)
    {
        if (null !== $modeFetch) {
            if (! isset(static::LIST_MODE_FETCH[ $modeFetch ])) {
                throw new LogicException(
                    [
                        'The `modeFetch` should be one of: '
                        . implode('|', array_keys(static::LIST_MODE_FETCH)),
                        $modeFetch,
                    ]
                );
            }
        }

        $this->modeFetch = $modeFetch ?? [];

        return $this;
    }

    /**
     * @return static
     */
    public function setModeSelectCount(?string $modeSelectCount)
    {
        if (null !== $modeSelectCount) {
            if (! isset(static::LIST_MODE_SELECT_COUNT[ $modeSelectCount ])) {
                throw new LogicException(
                    [
                        'The `modeSelectCount` should be one of: '
                        . implode('|', array_keys(static::LIST_MODE_SELECT_COUNT)),
                        $modeSelectCount,
                    ]
                );
            }
        }

        $this->modeSelectCount = $modeSelectCount ?? [];

        return $this;
    }

    /**
     * @return static
     */
    public function setModeResult(?string $modeResult)
    {
        if (null !== $modeResult) {
            if (! isset(static::LIST_MODE_RESULT[ $modeResult ])) {
                throw new LogicException(
                    [
                        'The `mode` should be one of: '
                        . implode('|', array_keys(static::LIST_MODE_RESULT)),
                        $modeResult,
                    ]
                );
            }
        }

        $this->modeResult = $modeResult ?? [];

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
    public function setLimitChunk(?int $limitChunk)
    {
        if (null !== $limitChunk) {
            if ($limitChunk <= 0) {
                throw new LogicException(
                    [ 'The `limitChunk` should be greater than 0', $limitChunk ]
                );
            }
        }

        $this->limitChunk = $limitChunk ?? $this->limitChunkDefault;

        return $this;
    }

    /**
     * @return static
     */
    public function setLimit(?int $limit)
    {
        if (null !== $limit) {
            if ($limit <= 0) {
                throw new LogicException(
                    [ 'The `limit` should be greater than 0', $limit ]
                );
            }
        }

        $this->limit = $limit ?? $this->limitDefault;

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
    public function setOffset(?int $offset)
    {
        if (null !== $offset) {
            if ($offset <= 0) {
                throw new LogicException(
                    [ 'The `offset` should be greater than 0', $offset ]
                );
            }
        }

        $this->offset = $offset ?? $this->offsetDefault;

        return $this;
    }

    /**
     * @return static
     */
    public function setOffsetValue(array $offsetValue = [])
    {
        $this->offsetValue = ([] !== $offsetValue)
            ? $offsetValue
            : [];

        return $this;
    }

    /**
     * @return static
     */
    public function setIncludeOffsetValue(?bool $includeOffsetValue)
    {
        $this->includeOffsetValue = $includeOffsetValue ?? $this->includeOffsetValueDefault;

        return $this;
    }


    public function hasOffsetValue(&$result = null) : bool
    {
        $result = null;

        if ([] !== $this->offsetValue) {
            [ $result ] = $this->offsetValue;

            return true;
        }

        return false;
    }


    public function getOffset() : int
    {
        return $this->offset;
    }

    public function getOffsetValue()
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
    public function setOffsetColumn(?string $offsetColumn)
    {
        if (null !== $offsetColumn) {
            if ('' === $offsetColumn) {
                throw new LogicException(
                    [
                        'The `offsetColumn` should be non-empty string',
                    ]
                );
            }
        }

        $this->offsetColumn = $offsetColumn ?? $this->offsetColumnDefault;

        return $this;
    }

    /**
     * @return static
     */
    public function setOffsetOperator(?string $offsetOperator)
    {
        if (null !== $offsetOperator) {
            if (! isset(static::LIST_OFFSET_OPERATOR[ $offsetOperator ])) {
                throw new LogicException(
                    [
                        'The `offsetOperator` should be one of: '
                        . implode('|', array_keys(static::LIST_OFFSET_OPERATOR)),
                    ]
                );
            }
        }

        $this->offsetOperator = $offsetOperator ?? $this->offsetOperatorDefault;

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
    public function setPerPage(?int $perPage)
    {
        if (null !== $perPage) {
            if ($perPage <= 0) {
                throw new LogicException(
                    [ 'The `perPage` should be greater than 0', $perPage ]
                );
            }
        }

        $this->perPage = $perPage ?? $this->perPageDefault;

        return $this;
    }

    /**
     * @return static
     */
    public function setPage(?int $page)
    {
        if (null !== $page) {
            if ($page <= 0) {
                throw new LogicException(
                    [ 'The `page` should be greater than 0', $page ]
                );
            }
        }

        $this->page = $page ?? $this->pageDefault;

        return $this;
    }

    /**
     * @return static
     */
    public function setPagesDelta(?int $pagesDelta)
    {
        if (null !== $pagesDelta) {
            if ($pagesDelta <= 0) {
                throw new LogicException(
                    [ 'The `pagesDelta` should be greater than 0', $pagesDelta ]
                );
            }
        }

        $this->pagesDelta = $pagesDelta ?? $this->pagesDeltaDefault;

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
    public function setTotalItems(?int $totalItems)
    {
        if (null !== $totalItems) {
            if ($totalItems <= 0) {
                throw new LogicException(
                    [ 'The `totalItems` should be greater than 0', $totalItems ]
                );
            }
        }

        $this->totalItems = $totalItems ?? $this->totalItemsDefault;

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


    /**
     * @return static
     */
    public function setTotalPages(?int $totalPages)
    {
        if (null !== $totalPages) {
            if ($totalPages <= 0) {
                throw new LogicException(
                    [ 'The `totalPages` should be greater than 0', $totalPages ]
                );
            }
        }

        $this->totalPages = $totalPages ?? $this->totalPagesDefault;

        return $this;
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
    public function withFetchModel()
    {
        $this->setModeFetch(static::MODE_FETCH_MODEL);

        return $this;
    }

    /**
     * @return static
     */
    public function withFetchPdo()
    {
        $this->setModeFetch(static::MODE_FETCH_PDO);

        return $this;
    }


    /**
     * @return static
     */
    public function withOffsetNative(
        ?int $offset = null
    )
    {
        $this->setModeOffset(static::MODE_OFFSET_NATIVE);

        if (null !== $offset) $this->setOffset($offset);

        return $this;
    }

    /**
     * @return static
     */
    public function withOffsetAfter(
        ?string $offsetColumn = null,
        ?string $offsetOperator = null,
        $offsetValue = null,
        ?bool $includeOffsetValue = null
    )
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
    public function withSelectCountNull()
    {
        $this->setModeSelectCount(static::MODE_SELECT_COUNT_NULL);

        return $this;
    }

    /**
     * @return static
     */
    public function withSelectCountNative()
    {
        $this->setModeSelectCount(static::MODE_SELECT_COUNT_NATIVE);

        return $this;
    }

    /**
     * @return static
     */
    public function withSelectCountExplain()
    {
        $this->setModeSelectCount(static::MODE_SELECT_COUNT_EXPLAIN);

        return $this;
    }


    /**
     * @return static
     */
    public function withResultChunk(
        ?int $limitChunk = null,
        ?int $limit = null
    )
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
        ?int $perPage = null,
        ?int $page = null,
        ?int $pagesDelta = null
    )
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
        ?int $limitChunk, ?int $limit = null,
        ?int $offset = null
    )
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
        ?int $limitChunk, ?int $limit = null,
        ?string $offsetColumn = null, ?string $offsetOperator = null, $offsetValue = null, ?bool $includeOffsetValue = null
    )
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
        ?int $limitChunk, ?int $limit = null,
        ?int $offset = null
    )
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
        ?int $limitChunk, ?int $limit = null,
        ?string $offsetColumn = null, ?string $offsetOperator = null, $offsetValue = null, ?bool $includeOffsetValue = null
    )
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
        ?int $perPage = null, ?int $page = null, ?int $pagesDelta = null,
        ?int $offset = null
    )
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
        ?int $perPage = null, ?int $page = null, ?int $pagesDelta = null,
        ?string $offsetColumn = null, ?string $offsetOperator = null, $offsetValue = null, ?bool $includeOffsetValue = null
    )
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
        ?int $perPage = null, ?int $page = null, ?int $pagesDelta = null,
        ?int $offset = null
    )
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
        ?int $perPage = null, ?int $page = null, ?int $pagesDelta = null,
        ?string $offsetColumn = null, ?string $offsetOperator = null, $offsetValue = null, ?bool $includeOffsetValue = null
    )
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
