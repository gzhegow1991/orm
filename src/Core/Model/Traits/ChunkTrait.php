<?php

namespace Gzhegow\Database\Core\Model\Traits;

use Gzhegow\Database\Core\Query\Chunks\ChunksBuilder;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection;


/**
 * @mixin EloquentModel
 */
trait ChunkTrait
{
    /**
     * @return ChunksBuilder
     */
    public static function chunks() : ChunksBuilder
    {
        $builder = ChunksBuilder::from(static::class);

        return $builder;
    }


    /**
     * @return \Generator<EloquentModelCollection<static>>
     */
    public static function chunksModelNativeForeach(
        int $limitChunk, int $limit = null,
        int $offset = null
    ) : \Generator
    {
        $builder = ChunksBuilder::from(static::class);

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
     * @return \Generator<EloquentModelCollection<static>>
     */
    public static function chunksModelAfterForeach(
        int $limitChunk, int $limit = null,
        string $offsetColumn = null, string $offsetOperator = null, $offsetValue = null, bool $includeOffsetValue = null
    ) : \Generator
    {
        $builder = ChunksBuilder::from(static::class);

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
     * @return \Generator<EloquentModelCollection<static>>
     */
    public static function chunksPdoNativeForeach(
        int $limitChunk, int $limit = null,
        int $offset = null
    ) : \Generator
    {
        $builder = ChunksBuilder::from(static::class);

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
     * @return \Generator<EloquentModelCollection<static>>
     */
    public static function chunksPdoAfterForeach(
        int $limitChunk, int $limit = null,
        string $offsetColumn = null, string $offsetOperator = null, $offsetValue = null, bool $includeOffsetValue = null
    ) : \Generator
    {
        $builder = ChunksBuilder::from(static::class);

        $builder
            ->chunksPdoAfterForeach(
                $limitChunk, $limit,
                $offsetColumn, $offsetOperator, $offsetValue, $includeOffsetValue
            )
        ;

        $generator = $builder->chunksForeach();

        return $generator;
    }


    public static function paginateModelNativeForeach(
        int $perPage = null, int $page = null, int $pagesDelta = null,
        int $offset = null
    ) : ChunksBuilder
    {
        $builder = ChunksBuilder::from(static::class);

        $builder
            ->paginateModelNativeForeach(
                $perPage, $page, $pagesDelta,
                $offset
            )
        ;

        return $builder;
    }

    public static function paginateModelAfterForeach(
        int $perPage = null, int $page = null, int $pagesDelta = null,
        string $offsetColumn = null, string $offsetOperator = null, $offsetValue = null, bool $includeOffsetValue = null
    ) : ChunksBuilder
    {
        $builder = ChunksBuilder::from(static::class);

        $builder
            ->paginateModelAfterForeach(
                $perPage, $page, $pagesDelta,
                $offsetColumn, $offsetOperator, $offsetValue, $includeOffsetValue
            )
        ;

        return $builder;
    }


    public static function paginatePdoNativeForeach(
        int $perPage = null, int $page = null, int $pagesDelta = null,
        int $offset = null
    ) : ChunksBuilder
    {
        $builder = ChunksBuilder::from(static::class);

        $builder
            ->paginatePdoNativeForeach(
                $perPage, $page, $pagesDelta,
                $offset
            )
        ;

        return $builder;
    }

    public static function paginatePdoAfterForeach(
        int $perPage = null, int $page = null, int $pagesDelta = null,
        string $offsetColumn = null, string $offsetOperator = null, $offsetValue = null, bool $includeOffsetValue = null
    ) : ChunksBuilder
    {
        $builder = ChunksBuilder::from(static::class);

        $builder
            ->paginatePdoAfterForeach(
                $perPage, $page, $pagesDelta,
                $offsetColumn, $offsetOperator, $offsetValue, $includeOffsetValue
            )
        ;

        return $builder;
    }
}
