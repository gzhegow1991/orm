<?php

namespace Gzhegow\Database\Core\Query\ModelQuery\Traits;

use Gzhegow\Database\Core\Query\Builder\ChunkBuilder;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelQueryBuilder;


/**
 * @template-covariant T of EloquentModel
 *
 * @mixin EloquentModelQueryBuilder
 */
trait ChunkTrait
{
    /**
     * @return ChunkBuilder
     */
    public function chunkBuilder() : ChunkBuilder
    {
        $builder = ChunkBuilder::from($this);

        $builder
            ->setModeYield(ChunkBuilder::MODE_YIELD_CHUNK)
        ;

        return $builder;
    }

    /**
     * @return \Generator<EloquentModelCollection<T>|T[]>
     */
    public function chunkModelNativeForeach(
        int $limitChunk, int $limit = null,
        int $offset = null
    ) : \Generator
    {
        $builder = ChunkBuilder::from($this);

        $builder
            ->fetchModel()
            ->offsetNative($offset)
            ->yieldChunk($limitChunk, $limit)
        ;

        $generator = $builder->foreach();

        return $generator;
    }

    /**
     * @return \Generator<EloquentModelCollection<T>|T[]>
     */
    public function chunkModelAfterForeach(
        int $limitChunk, int $limit = null,
        string $offsetColumn = null, string $offsetOperator = null, $offsetValue = null, bool $includeOffsetValue = null
    ) : \Generator
    {
        $builder = ChunkBuilder::from($this);

        $builder
            ->fetchModel()
            ->offsetAfter($offsetColumn, $offsetOperator, $offsetValue, $includeOffsetValue)
            ->yieldChunk($limitChunk, $limit)
        ;

        $generator = $builder->foreach();

        return $generator;
    }

    /**
     * @return \Generator<EloquentModelCollection<T>|T[]>
     */
    public function chunkPdoNativeForeach(
        int $limitChunk, int $limit = null,
        int $offset = null
    ) : \Generator
    {
        $builder = ChunkBuilder::from($this);

        $builder
            ->fetchPdo()
            ->offsetNative($offset)
            ->yieldChunk($limitChunk, $limit)
        ;

        $generator = $builder->foreach();

        return $generator;
    }

    /**
     * @return \Generator<EloquentModelCollection<T>|T[]>
     */
    public function chunkPdoAfterForeach(
        int $limitChunk, int $limit = null,
        string $offsetColumn = null, string $offsetOperator = null, $offsetValue = null, bool $includeOffsetValue = null
    ) : \Generator
    {
        $builder = ChunkBuilder::from($this);

        $builder
            ->fetchPdo()
            ->offsetAfter($offsetColumn, $offsetOperator, $offsetValue, $includeOffsetValue)
            ->yieldChunk($limitChunk, $limit)
        ;

        $generator = $builder->foreach();

        return $generator;
    }


    /**
     * @return ChunkBuilder
     */
    public function paginateBuilder() : ChunkBuilder
    {
        $builder = ChunkBuilder::from($this);

        $builder
            ->setModeYield(ChunkBuilder::MODE_YIELD_PAGE)
        ;

        return $builder;
    }

    /**
     * @return \Generator<array>
     */
    public function paginateModelNativeForeach(
        int $perPage = null, int $page = null, int $pagesCount = null,
        int $offset = null
    ) : \Generator
    {
        $builder = ChunkBuilder::from($this);

        $builder
            ->fetchModel()
            ->offsetNative($offset)
            ->yieldPage($perPage, $page, $pagesCount)
        ;

        $generator = $builder->foreach();

        return $generator;
    }

    /**
     * @return \Generator<array>
     */
    public function paginateModelAfterForeach(
        int $perPage = null, int $page = null, int $pagesCount = null,
        string $offsetColumn = null, string $offsetOperator = null, $offsetValue = null, bool $includeOffsetValue = null
    ) : \Generator
    {
        $builder = ChunkBuilder::from($this);

        $builder
            ->fetchModel()
            ->offsetAfter($offsetColumn, $offsetOperator, $offsetValue, $includeOffsetValue)
            ->yieldPage($perPage, $page, $pagesCount)
        ;

        $generator = $builder->foreach();

        return $generator;
    }

    /**
     * @return \Generator<array>
     */
    public function paginatePdoNativeForeach(
        int $perPage = null, int $page = null, int $pagesCount = null,
        int $offset = null
    ) : \Generator
    {
        $builder = ChunkBuilder::from($this);

        $builder
            ->fetchPdo()
            ->offsetNative($offset)
            ->yieldPage($perPage, $page, $pagesCount)
        ;

        $generator = $builder->foreach();

        return $generator;
    }

    /**
     * @return \Generator<array>
     */
    public function paginatePdoAfterForeach(
        int $perPage = null, int $page = null, int $pagesCount = null,
        string $offsetColumn = null, string $offsetOperator = null, $offsetValue = null, bool $includeOffsetValue = null
    ) : \Generator
    {
        $builder = ChunkBuilder::from($this);

        $builder
            ->fetchPdo()
            ->offsetAfter($offsetColumn, $offsetOperator, $offsetValue, $includeOffsetValue)
            ->yieldPage($perPage, $page, $pagesCount)
        ;

        $generator = $builder->foreach();

        return $generator;
    }
}
