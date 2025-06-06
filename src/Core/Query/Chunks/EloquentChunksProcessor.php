<?php

namespace Gzhegow\Orm\Core\Query\Chunks;

use Gzhegow\Orm\Exception\LogicException;
use Gzhegow\Orm\Exception\RuntimeException;
use Illuminate\Support\Collection as EloquentSupportCollection;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Base\EloquentModel;
use Gzhegow\Orm\Package\Illuminate\Database\EloquentPdoQueryBuilder;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelCollection;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelQueryBuilder;


/**
 * @template-covariant T of EloquentModel
 */
class EloquentChunksProcessor implements EloquentChunksProcessorInterface
{
    /**
     * @return \Generator<EloquentModelCollection<T>>
     */
    public function chunksModelNativeForeach(EloquentChunksBuilder $builder) : \Generator
    {
        if (EloquentChunksBuilder::MODE_RESULT_CHUNK !== $builder->getModeResult()) {
            throw new RuntimeException([ 'The `modeResult` should be: ' . EloquentChunksBuilder::MODE_RESULT_CHUNK, $builder->getModeResult() ]);
        }
        if (EloquentChunksBuilder::MODE_FETCH_MODEL !== $builder->getModeFetch()) {
            throw new RuntimeException([ 'The `modeFetch` should be: ' . EloquentChunksBuilder::MODE_FETCH_MODEL, $builder->getModeFetch() ]);
        }
        if (EloquentChunksBuilder::MODE_OFFSET_NATIVE !== $builder->getModeOffset()) {
            throw new RuntimeException([ 'The `modeOffset` should be: ' . EloquentChunksBuilder::MODE_OFFSET_NATIVE, $builder->getModeOffset() ]);
        }

        $generator = $this->doChunksModelNativeForeach($builder);

        return $generator;
    }

    /**
     * @return \Generator<EloquentModelCollection<T>>
     */
    private function doChunksModelNativeForeach(EloquentChunksBuilder $builder) : \Generator
    {
        $modelQuery = $builder->getModelQuery();

        $limitChunk = $builder->getLimitChunk();

        $limit = $builder->hasLimit();
        $offset = $builder->getOffset();

        $total = $limit ?? INF;
        $left = $total;

        do {
            $queryClone = clone $modelQuery;

            $limitChunkCurrent = min($left, $limitChunk);
            if (! $limitChunkCurrent) {
                break;
            }

            if ($offset > 0) {
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
            $offset = $offset + $modelsCount;
        } while ( $left && ($modelsCount === $limitChunkCurrent) );
    }


    /**
     * @return \Generator<EloquentSupportCollection<\stdClass>
     */
    public function chunksPdoNativeForeach(EloquentChunksBuilder $builder) : \Generator
    {
        if (EloquentChunksBuilder::MODE_RESULT_CHUNK !== $builder->getModeResult()) {
            throw new RuntimeException([ 'The `modeResult` should be: ' . EloquentChunksBuilder::MODE_RESULT_CHUNK, $builder->getModeResult() ]);
        }
        if (EloquentChunksBuilder::MODE_FETCH_PDO !== $builder->getModeFetch()) {
            throw new RuntimeException([ 'The `modeFetch` should be: ' . EloquentChunksBuilder::MODE_FETCH_PDO, $builder->getModeFetch() ]);
        }
        if (EloquentChunksBuilder::MODE_OFFSET_NATIVE !== $builder->getModeOffset()) {
            throw new RuntimeException([ 'The `modeOffset` should be: ' . EloquentChunksBuilder::MODE_OFFSET_NATIVE, $builder->getModeOffset() ]);
        }

        $generator = $this->doChunksPdoNativeForeach($builder);

        return $generator;
    }

    /**
     * @return \Generator<EloquentSupportCollection<\stdClass>
     */
    private function doChunksPdoNativeForeach(EloquentChunksBuilder $builder) : \Generator
    {
        $pdoQuery = $builder->getPdoQuery();

        $limitChunk = $builder->getLimitChunk();

        $limit = $builder->hasLimit();
        $offset = $builder->getOffset();

        $total = $limit ?? INF;
        $left = $total;

        do {
            $queryClone = clone $pdoQuery;

            $limitChunkCurrent = min($left, $limitChunk);
            if (! $limitChunkCurrent) {
                break;
            }

            if ($offset > 0) {
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
            $offset = $offset + $rowsCount;
        } while ( (0 !== $left) && ($rowsCount === $limitChunkCurrent) );
    }


    /**
     * @return \Generator<EloquentModelCollection<T>>
     */
    public function chunksModelAfterForeach(EloquentChunksBuilder $builder) : \Generator
    {
        if (EloquentChunksBuilder::MODE_RESULT_CHUNK !== $builder->getModeResult()) {
            throw new RuntimeException([ 'The `modeResult` should be: ' . EloquentChunksBuilder::MODE_RESULT_CHUNK, $builder->getModeResult() ]);
        }
        if (EloquentChunksBuilder::MODE_FETCH_MODEL !== $builder->getModeFetch()) {
            throw new RuntimeException([ 'The `modeFetch` should be: ' . EloquentChunksBuilder::MODE_FETCH_MODEL, $builder->getModeFetch() ]);
        }
        if (EloquentChunksBuilder::MODE_OFFSET_AFTER !== $builder->getModeOffset()) {
            throw new RuntimeException([ 'The `modeOffset` should be: ' . EloquentChunksBuilder::MODE_OFFSET_AFTER, $builder->getModeOffset() ]);
        }

        $generator = $this->doChunksModelAfterForeach($builder);

        return $generator;
    }

    /**
     * @return \Generator<EloquentModelCollection<T>>
     */
    private function doChunksModelAfterForeach(EloquentChunksBuilder $builder) : \Generator
    {
        $modelQuery = $builder->getModelQuery();
        $pdoQuery = $builder->getPdoQuery();

        $limitChunk = $builder->getLimitChunk();

        $limit = $builder->hasLimit();
        $offset = $builder->getOffset();

        $offsetColumn = $builder->getOffsetColumn();
        $offsetOperator = $builder->getOffsetOperator();
        $offsetValueStart = $builder->getOffsetValue();
        $includeOffsetValueStart = $builder->getIncludeOffsetValue();

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
            if ($offsetOperator === EloquentChunksBuilder::OFFSET_OPERATOR_GT) {
                $offsetOperatorFirst = EloquentChunksBuilder::OFFSET_OPERATOR_GTE;
            }
            if ($offsetOperator === EloquentChunksBuilder::OFFSET_OPERATOR_LT) {
                $offsetOperatorFirst = EloquentChunksBuilder::OFFSET_OPERATOR_LTE;
            }
        }

        $offsetOrder = 'asc';
        if (false
            || ($offsetOperator === EloquentChunksBuilder::OFFSET_OPERATOR_LT)
            || ($offsetOperator === EloquentChunksBuilder::OFFSET_OPERATOR_LTE)
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
        } while ( (0 !== $left) && ($modelsCount === $limitChunkCurrent) );
    }


    /**
     * @return \Generator<EloquentSupportCollection<\stdClass>
     */
    public function chunksPdoAfterForeach(EloquentChunksBuilder $builder) : \Generator
    {
        if (EloquentChunksBuilder::MODE_RESULT_CHUNK !== $builder->getModeResult()) {
            throw new RuntimeException([ 'The `modeResult` should be: ' . EloquentChunksBuilder::MODE_RESULT_CHUNK, $builder->getModeResult() ]);
        }
        if (EloquentChunksBuilder::MODE_FETCH_PDO !== $builder->getModeFetch()) {
            throw new RuntimeException([ 'The `modeFetch` should be: ' . EloquentChunksBuilder::MODE_FETCH_PDO, $builder->getModeFetch() ]);
        }
        if (EloquentChunksBuilder::MODE_OFFSET_AFTER !== $builder->getModeOffset()) {
            throw new RuntimeException([ 'The `modeOffset` should be: ' . EloquentChunksBuilder::MODE_OFFSET_AFTER, $builder->getModeOffset() ]);
        }

        $generator = $this->doChunksPdoAfterForeach($builder);

        return $generator;
    }

    /**
     * @return \Generator<EloquentSupportCollection<\stdClass>
     */
    private function doChunksPdoAfterForeach(EloquentChunksBuilder $builder) : \Generator
    {
        $pdoQuery = $builder->getPdoQuery();

        $limitChunk = $builder->getLimitChunk();

        $limit = $builder->hasLimit();
        $offset = $builder->getOffset();

        $offsetColumn = $builder->getOffsetColumn();
        $offsetOperator = $builder->getOffsetOperator();
        $offsetValueStart = $builder->getOffsetValue();
        $includeOffsetValueStart = $builder->getIncludeOffsetValue();

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
            if ($offsetOperator === EloquentChunksBuilder::OFFSET_OPERATOR_GT) {
                $offsetOperatorFirst = EloquentChunksBuilder::OFFSET_OPERATOR_GTE;
            }
            if ($offsetOperator === EloquentChunksBuilder::OFFSET_OPERATOR_LT) {
                $offsetOperatorFirst = EloquentChunksBuilder::OFFSET_OPERATOR_LTE;
            }
        }

        $offsetOrder = 'asc';
        if (false
            || ($offsetOperator === EloquentChunksBuilder::OFFSET_OPERATOR_LT)
            || ($offsetOperator === EloquentChunksBuilder::OFFSET_OPERATOR_LTE)
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
        } while ( (0 !== $left) && ($rowsCount === $limitChunkCurrent) );
    }


    public function paginateModelNativeForeach(EloquentChunksBuilder $builder) : EloquentChunksPaginateResult
    {
        if (EloquentChunksBuilder::MODE_RESULT_PAGINATE !== $builder->getModeResult()) {
            throw new RuntimeException([ 'The `modeResult` should be: ' . EloquentChunksBuilder::MODE_RESULT_PAGINATE, $builder->getModeResult() ]);
        }
        if (EloquentChunksBuilder::MODE_FETCH_MODEL !== $builder->getModeFetch()) {
            throw new RuntimeException([ 'The `modeFetch` should be: ' . EloquentChunksBuilder::MODE_FETCH_MODEL, $builder->getModeFetch() ]);
        }
        if (EloquentChunksBuilder::MODE_OFFSET_NATIVE !== $builder->getModeOffset()) {
            throw new RuntimeException([ 'The `modeOffset` should be: ' . EloquentChunksBuilder::MODE_OFFSET_NATIVE, $builder->getModeOffset() ]);
        }

        $this->paginateCalculateLimits($builder);

        $generator = $this->doChunksModelNativeForeach($builder);

        $result = $this->paginateGenerateResult($builder, $generator);

        return $result;
    }

    public function paginatePdoNativeForeach(EloquentChunksBuilder $builder) : EloquentChunksPaginateResult
    {
        if (EloquentChunksBuilder::MODE_RESULT_PAGINATE !== $builder->getModeResult()) {
            throw new RuntimeException([ 'The `modeResult` should be: ' . EloquentChunksBuilder::MODE_RESULT_PAGINATE, $builder->getModeResult() ]);
        }
        if (EloquentChunksBuilder::MODE_FETCH_PDO !== $builder->getModeFetch()) {
            throw new RuntimeException([ 'The `modeFetch` should be: ' . EloquentChunksBuilder::MODE_FETCH_PDO, $builder->getModeFetch() ]);
        }
        if (EloquentChunksBuilder::MODE_OFFSET_NATIVE !== $builder->getModeOffset()) {
            throw new RuntimeException([ 'The `modeOffset` should be: ' . EloquentChunksBuilder::MODE_OFFSET_NATIVE, $builder->getModeOffset() ]);
        }

        $this->paginateCalculateLimits($builder);

        $generator = $this->doChunksPdoNativeForeach($builder);

        $result = $this->paginateGenerateResult($builder, $generator);

        return $result;
    }


    public function paginateModelAfterForeach(EloquentChunksBuilder $builder) : EloquentChunksPaginateResult
    {
        if (EloquentChunksBuilder::MODE_RESULT_PAGINATE !== $builder->getModeResult()) {
            throw new RuntimeException([ 'The `modeResult` should be: ' . EloquentChunksBuilder::MODE_RESULT_PAGINATE, $builder->getModeResult() ]);
        }
        if (EloquentChunksBuilder::MODE_FETCH_MODEL !== $builder->getModeFetch()) {
            throw new RuntimeException([ 'The `modeFetch` should be: ' . EloquentChunksBuilder::MODE_FETCH_MODEL, $builder->getModeFetch() ]);
        }
        if (EloquentChunksBuilder::MODE_OFFSET_AFTER !== $builder->getModeOffset()) {
            throw new RuntimeException([ 'The `modeOffset` should be: ' . EloquentChunksBuilder::MODE_OFFSET_AFTER, $builder->getModeOffset() ]);
        }

        $this->paginateCalculateLimits($builder);

        $generator = $this->doChunksModelAfterForeach($builder);

        $result = $this->paginateGenerateResult($builder, $generator);

        return $result;
    }

    public function paginatePdoAfterForeach(EloquentChunksBuilder $builder) : EloquentChunksPaginateResult
    {
        if (EloquentChunksBuilder::MODE_RESULT_PAGINATE !== $builder->getModeResult()) {
            throw new RuntimeException([ 'The `modeResult` should be: ' . EloquentChunksBuilder::MODE_RESULT_PAGINATE, $builder->getModeResult() ]);
        }
        if (EloquentChunksBuilder::MODE_FETCH_PDO !== $builder->getModeFetch()) {
            throw new RuntimeException([ 'The `modeFetch` should be: ' . EloquentChunksBuilder::MODE_FETCH_PDO, $builder->getModeFetch() ]);
        }
        if (EloquentChunksBuilder::MODE_OFFSET_AFTER !== $builder->getModeOffset()) {
            throw new RuntimeException([ 'The `modeOffset` should be: ' . EloquentChunksBuilder::MODE_OFFSET_AFTER, $builder->getModeOffset() ]);
        }

        $this->paginateCalculateLimits($builder);

        $generator = $this->doChunksPdoAfterForeach($builder);

        $result = $this->paginateGenerateResult($builder, $generator);

        return $result;
    }


    protected function paginateCalculateLimits(EloquentChunksBuilder $builder) : void
    {
        $perPage = $builder->getPerPage();
        $page = $builder->getPage();
        $pagesDelta = $builder->hasPagesDelta();

        $limitChunk = $perPage;
        $limit = ($pagesDelta !== null) ? (($pagesDelta + 1) * $perPage) : null;
        $offset = $page ? (($page - 1) * $perPage) : 0;

        $builder->setLimitChunk($limitChunk);
        $builder->setLimit($limit);
        $builder->setOffset($offset);
    }

    protected function paginateGenerateResult(EloquentChunksBuilder $builder, \Generator $generator) : EloquentChunksPaginateResult
    {
        $modeFetch = $builder->getModeFetch();
        $modeSelectCount = $builder->getModeSelectCount();

        $page = $builder->getPage();
        $perPage = $builder->getPerPage();
        $pagesDelta = $builder->hasPagesDelta();

        $totalItems = $builder->hasTotalItems();
        $totalPages = $builder->hasTotalPages();

        $queryModel = null;
        $queryPdo = null;
        if (EloquentChunksBuilder::MODE_FETCH_MODEL === $modeFetch) {
            $queryModel = $builder->getModelQuery();
        }
        if (EloquentChunksBuilder::MODE_FETCH_PDO === $modeFetch) {
            $queryPdo = $builder->getPdoQuery();
        }

        if (null === $totalItems) {
            $mapSelectCount = [];
            $mapSelectCount[ EloquentChunksBuilder::MODE_SELECT_COUNT_NATIVE ][ EloquentChunksBuilder::MODE_FETCH_MODEL ] = [ $queryModel, 'count' ];
            $mapSelectCount[ EloquentChunksBuilder::MODE_SELECT_COUNT_NATIVE ][ EloquentChunksBuilder::MODE_FETCH_PDO ] = [ $queryPdo, 'count' ];
            $mapSelectCount[ EloquentChunksBuilder::MODE_SELECT_COUNT_EXPLAIN ][ EloquentChunksBuilder::MODE_FETCH_MODEL ] = [ $queryModel, 'countExplain' ];
            $mapSelectCount[ EloquentChunksBuilder::MODE_SELECT_COUNT_EXPLAIN ][ EloquentChunksBuilder::MODE_FETCH_PDO ] = [ $queryPdo, 'countExplain' ];

            $fn = $mapSelectCount[ $modeSelectCount ][ $modeFetch ] ?? null;
            if (null !== $fn) {
                $totalItems = $fn();
            }
        }

        if (null === $totalPages) {
            if (null !== $totalItems) {
                $totalPages = (int) ceil($totalItems / $perPage);
            }
        }

        $from = null;
        $to = null;

        $pagesAbsolute = [];
        $pagesRelative = [];

        $items = null;
        $itemsCount = null;

        $isFirst = true;
        $pageUp = $page;
        while ( $generator->valid() ) {
            /**
             * @var EloquentModelCollection<T> $collection
             */

            $collection = $generator->current();
            $collectionCount = $collection->count();

            if ($isFirst) {
                $pagesAbsolute[ $page ] = $collectionCount;

                $items = $collection;
                $itemsCount = $collectionCount;

                if ($collectionCount) {
                    $from = ($page - 1) * $perPage;
                    $to = $from + $collectionCount;
                }

            } else {
                $pagesAbsolute[ ++$pageUp ] = $collectionCount;

                if ($collectionCount < $perPage) {
                    $totalPages = $pageUp;
                    $totalItems = (($pageUp - 1) * $perPage) + $collectionCount;
                }
            }

            if ($isFirst) {
                $isFirst = false;
            }

            $generator->next();
        }

        $hasItems = (null !== $itemsCount);
        $hasTotalItems = (null !== $totalItems);
        $hasTotalPages = (null !== $totalPages);

        if ($hasItems || $hasTotalItems || $hasTotalPages) {
            if (! isset($pagesAbsolute[ 1 ])) {
                $pagesAbsolute[ 1 ] = $perPage;
            }
        }
        if (! isset($pagesAbsolute[ $page ])) {
            $pagesAbsolute[ $page ] = null;
        }
        if ($hasTotalPages) {
            if (! isset($pagesAbsolute[ $totalPages ])) {
                $pagesAbsolute[ $totalPages ] = true;

                if ($hasTotalItems) {
                    $pagesAbsolute[ $totalPages ] = (int) floor($totalItems / $perPage);
                }
            }
        }

        $pageUp = $page;
        $pageDown = $page;
        for ( $i = 0; $i < $pagesDelta; $i++ ) {
            $pageDown--;
            if ($pageDown > 0) {
                if (! isset($pagesAbsolute[ $pageDown ])) {
                    if ($hasItems) {
                        $pagesAbsolute[ $pageDown ] = $perPage;
                    }
                }
            }

            $pageUp++;
            if ($pageUp > 0) {
                if (! isset($pagesAbsolute[ $pageUp ])) {
                    if ($hasTotalPages) {
                        if ($pageUp <= $totalPages) {
                            $pagesAbsolute[ $pageUp ] = null;
                        }
                    }
                }
            }
        }

        ksort($pagesAbsolute);

        $pagesRelative[ 'first' ] = $pagesAbsolute[ 1 ] ?? null;
        $pagesRelative[ 'previous' ] = $pagesAbsolute[ $page - 1 ] ?? null;
        $pagesRelative[ 'current' ] = $pagesAbsolute[ $page ] ?? null;
        $pagesRelative[ 'next' ] = $pagesAbsolute[ $page + 1 ] ?? null;
        $pagesRelative[ 'last' ] = $pagesAbsolute[ $totalPages ] ?? null;

        if ($page === 1) {
            $pagesRelative[ 'first' ] = null;
        }
        if (($page - 1) === 1) {
            $pagesRelative[ 'previous' ] = null;
        }
        if (($page + 1) === $totalPages) {
            $pagesRelative[ 'next' ] = null;
        }
        if ($page === $totalPages) {
            $pagesRelative[ 'last' ] = null;
        }

        $result = new EloquentChunksPaginateResult();
        $result->totalItems = $totalItems;
        $result->totalPages = $totalPages;
        $result->page = $page;
        $result->perPage = $perPage;
        $result->pagesDelta = $pagesDelta;
        $result->from = $from;
        $result->to = $to;
        $result->pagesAbsolute = $pagesAbsolute;
        $result->pagesRelative = $pagesRelative;
        $result->items = $items;

        return $result;
    }
}
