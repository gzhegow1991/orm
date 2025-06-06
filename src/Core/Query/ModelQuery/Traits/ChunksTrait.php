<?php

namespace Gzhegow\Orm\Core\Query\ModelQuery\Traits;

use Gzhegow\Orm\Core\Query\Chunks\EloquentChunksBuilder;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Base\EloquentModel;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelCollection;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelQueryBuilder;


/**
 * @template-covariant T of EloquentModel
 *
 * @mixin EloquentModelQueryBuilder
 */
trait ChunksTrait
{
    /**
     * @return EloquentChunksBuilder
     */
    public function chunks() : EloquentChunksBuilder
    {
        $builder = EloquentChunksBuilder::fromModelQuery($this);

        return $builder;
    }


    /**
     * @return \Generator<EloquentModelCollection<T>>
     */
    public function chunksModelNativeForeach(
        int $limitChunk, ?int $limit = null,
        ?int $offset = null
    ) : \Generator
    {
        $builder = EloquentChunksBuilder::fromModelQuery($this);

        $builder
            ->chunksModelNativeForeach(
                $limitChunk, $limit,
                $offset
            )
        ;

        $generator = $builder->chunksForeach();

        return $generator;
    }

    /**
     * @return \Generator<EloquentModelCollection<T>>
     */
    public function chunksModelAfterForeach(
        int $limitChunk, ?int $limit = null,
        ?string $offsetColumn = null, ?string $offsetOperator = null, $offsetValue = null, ?bool $includeOffsetValue = null
    ) : \Generator
    {
        $builder = EloquentChunksBuilder::fromModelQuery($this);

        $builder
            ->chunksModelAfterForeach(
                $limitChunk, $limit,
                $offsetColumn, $offsetOperator, $offsetValue, $includeOffsetValue
            )
        ;

        $generator = $builder->chunksForeach();

        return $generator;
    }


    /**
     * @return \Generator<EloquentModelCollection<T>>
     */
    public function chunksPdoNativeForeach(
        int $limitChunk, ?int $limit = null,
        ?int $offset = null
    ) : \Generator
    {
        $builder = EloquentChunksBuilder::fromModelQuery($this);

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
     * @return \Generator<EloquentModelCollection<T>>
     */
    public function chunksPdoAfterForeach(
        int $limitChunk, ?int $limit = null,
        ?string $offsetColumn = null, ?string $offsetOperator = null, $offsetValue = null, ?bool $includeOffsetValue = null
    ) : \Generator
    {
        $builder = EloquentChunksBuilder::fromModelQuery($this);

        $builder
            ->chunksPdoAfterForeach(
                $limitChunk, $limit,
                $offsetColumn, $offsetOperator, $offsetValue, $includeOffsetValue
            )
        ;

        $generator = $builder->chunksForeach();

        return $generator;
    }


    public function paginateModelNativeForeach(
        ?int $perPage = null, ?int $page = null, ?int $pagesDelta = null,
        ?int $offset = null
    ) : EloquentChunksBuilder
    {
        $builder = EloquentChunksBuilder::fromModelQuery($this);

        $builder
            ->paginateModelNativeForeach(
                $perPage, $page, $pagesDelta,
                $offset
            )
        ;

        return $builder;
    }

    public function paginateModelAfterForeach(
        ?int $perPage = null, ?int $page = null, ?int $pagesDelta = null,
        ?string $offsetColumn = null, ?string $offsetOperator = null, $offsetValue = null, ?bool $includeOffsetValue = null
    ) : EloquentChunksBuilder
    {
        $builder = EloquentChunksBuilder::fromModelQuery($this);

        $builder
            ->paginateModelAfterForeach(
                $perPage, $page, $pagesDelta,
                $offsetColumn, $offsetOperator, $offsetValue, $includeOffsetValue
            )
        ;

        return $builder;
    }


    public function paginatePdoNativeForeach(
        ?int $perPage = null, ?int $page = null, ?int $pagesDelta = null,
        ?int $offset = null
    ) : EloquentChunksBuilder
    {
        $builder = EloquentChunksBuilder::fromModelQuery($this);

        $builder
            ->paginatePdoNativeForeach(
                $perPage, $page, $pagesDelta,
                $offset
            )
        ;

        return $builder;
    }

    public function paginatePdoAfterForeach(
        ?int $perPage = null, ?int $page = null, ?int $pagesDelta = null,
        ?string $offsetColumn = null, ?string $offsetOperator = null, $offsetValue = null, ?bool $includeOffsetValue = null
    ) : EloquentChunksBuilder
    {
        $builder = EloquentChunksBuilder::fromModelQuery($this);

        $builder
            ->paginatePdoAfterForeach(
                $perPage, $page, $pagesDelta,
                $offsetColumn, $offsetOperator, $offsetValue, $includeOffsetValue
            )
        ;

        return $builder;
    }
}
