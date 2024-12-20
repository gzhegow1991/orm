<?php

namespace Gzhegow\Database\Core\Query\PdoQuery\Traits;

use Gzhegow\Database\Core\Query\Builder\ChunkBuilder;
use Illuminate\Support\Collection as EloquentSupportCollection;
use Gzhegow\Database\Package\Illuminate\Database\EloquentPdoQueryBuilder;


/**
 * @mixin EloquentPdoQueryBuilder
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
            ->setModeFetch(ChunkBuilder::MODE_FETCH_PDO)
            ->setModeYield(ChunkBuilder::MODE_YIELD_CHUNK)
        ;

        return $builder;
    }

    /**
     * @return \Generator<EloquentSupportCollection<\stdClass>|\stdClass[]>
     */
    public function chunkNativeForeach(
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
     * @return \Generator<int, EloquentSupportCollection<\stdClass>|\stdClass[]>
     */
    public function chunkAfterForeach(
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
            ->setModeFetch(ChunkBuilder::MODE_FETCH_PDO)
            ->setModeYield(ChunkBuilder::MODE_YIELD_PAGE)
        ;

        return $builder;
    }

    /**
     * @return \Generator<array>
     */
    public function paginateNativeForeach(
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
    public function paginateAfterForeach(
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
