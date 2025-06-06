<?php

namespace Gzhegow\Orm\Core\Query\PdoQuery\Traits;

use Gzhegow\Orm\Core\Query\Chunks\EloquentChunksBuilder;
use Illuminate\Support\Collection as EloquentSupportCollection;
use Gzhegow\Orm\Package\Illuminate\Database\EloquentPdoQueryBuilder;


/**
 * @mixin EloquentPdoQueryBuilder
 */
trait ChunksTrait
{
    /**
     * @return EloquentChunksBuilder
     */
    public function chunks() : EloquentChunksBuilder
    {
        $builder = EloquentChunksBuilder::fromPdoQuery($this);

        return $builder;
    }


    /**
     * @return \Generator<EloquentSupportCollection<\stdClass>
     */
    public function chunksNativeForeach(
        int $limitChunk, ?int $limit = null,
        ?int $offset = null
    ) : \Generator
    {
        $builder = EloquentChunksBuilder::fromPdoQuery($this);

        $builder
            ->chunksPdoNativeForeach(
                $limitChunk, $limit,
                $offset
            )
        ;

        $generator = $builder->chunksForeach();

        return $generator;
    }

    /**
     * @return \Generator<int, EloquentSupportCollection<\stdClass>
     */
    public function chunksAfterForeach(
        ?int $limitChunk, ?int $limit = null,
        ?string $offsetColumn = null, ?string $offsetOperator = null, $offsetValue = null, ?bool $includeOffsetValue = null
    ) : \Generator
    {
        $builder = EloquentChunksBuilder::fromPdoQuery($this);

        $builder
            ->chunksPdoAfterForeach(
                $limitChunk, $limit,
                $offsetColumn, $offsetOperator, $offsetValue, $includeOffsetValue
            )
        ;

        $generator = $builder->chunksForeach();

        return $generator;
    }


    public function paginateNativeForeach(
        ?int $perPage = null, ?int $page = null, ?int $pagesDelta = null,
        ?int $offset = null
    ) : EloquentChunksBuilder
    {
        $builder = EloquentChunksBuilder::fromPdoQuery($this);

        $builder
            ->paginatePdoNativeForeach(
                $perPage, $page, $pagesDelta,
                $offset
            )
        ;

        return $builder;
    }

    public function paginateAfterForeach(
        ?int $perPage = null, ?int $page = null, ?int $pagesDelta = null,
        ?string $offsetColumn = null, ?string $offsetOperator = null, $offsetValue = null, ?bool $includeOffsetValue = null
    ) : EloquentChunksBuilder
    {
        $builder = EloquentChunksBuilder::fromPdoQuery($this);

        $builder
            ->paginatePdoAfterForeach(
                $perPage, $page, $pagesDelta,
                $offsetColumn, $offsetOperator, $offsetValue, $includeOffsetValue
            )
        ;

        return $builder;
    }
}
