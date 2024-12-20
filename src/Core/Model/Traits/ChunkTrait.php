<?php

namespace Gzhegow\Database\Core\Model\Traits;

use Gzhegow\Database\Core\Query\Builder\ChunkBuilder;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection;


/**
 * @mixin EloquentModel
 */
trait ChunkTrait
{
    /**
     * @return ChunkBuilder
     */
    public static function chunkBuilder() : ChunkBuilder
    {
        $builder = ChunkBuilder::from(static::class);

        $builder
            ->setModeYield(ChunkBuilder::MODE_YIELD_CHUNK)
        ;

        return $builder;
    }

    /**
     * @return \Generator<EloquentModelCollection<static>|static[]>
     */
    public static function chunkModelNativeForeach(
        int $limitChunk, int $limit = null,
        int $offset = null
    ) : \Generator
    {
        $builder = ChunkBuilder::from(static::class);

        $builder
            ->fetchModel()
            ->offsetNative($offset)
            ->yieldChunk($limitChunk, $limit)
        ;

        $generator = $builder->foreach();

        return $generator;
    }

    /**
     * @return \Generator<EloquentModelCollection<static>|static[]>
     */
    public static function chunkModelAfterForeach(
        int $limitChunk, int $limit = null,
        string $offsetColumn = null, string $offsetOperator = null, $offsetValue = null, bool $includeOffsetValue = null
    ) : \Generator
    {
        $builder = ChunkBuilder::from(static::class);

        $builder
            ->fetchModel()
            ->offsetAfter($offsetColumn, $offsetOperator, $offsetValue, $includeOffsetValue)
            ->yieldChunk($limitChunk, $limit)
        ;

        $generator = $builder->foreach();

        return $generator;
    }

    /**
     * @return \Generator<EloquentModelCollection<static>|static[]>
     */
    public static function chunkPdoNativeForeach(
        int $limitChunk, int $limit = null,
        int $offset = null
    ) : \Generator
    {
        $builder = ChunkBuilder::from(static::class);

        $builder
            ->fetchPdo()
            ->offsetNative($offset)
            ->yieldChunk($limitChunk, $limit)
        ;

        $generator = $builder->foreach();

        return $generator;
    }

    /**
     * @return \Generator<EloquentModelCollection<static>|static[]>
     */
    public static function chunkPdoAfterForeach(
        int $limitChunk, int $limit = null,
        string $offsetColumn = null, string $offsetOperator = null, $offsetValue = null, bool $includeOffsetValue = null
    ) : \Generator
    {
        $builder = ChunkBuilder::from(static::class);

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
    public static function paginateBuilder() : ChunkBuilder
    {
        $builder = ChunkBuilder::from(static::class);

        $builder
            ->setModeYield(ChunkBuilder::MODE_YIELD_PAGE)
        ;

        return $builder;
    }

    /**
     * @return \Generator<array>
     */
    public static function paginateModelNativeForeach(
        int $perPage = null, int $page = null, int $pagesCount = null,
        int $offset = null
    ) : \Generator
    {
        $builder = ChunkBuilder::from(static::class);

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
    public static function paginateModelAfterForeach(
        int $perPage = null, int $page = null, int $pagesCount = null,
        string $offsetColumn = null, string $offsetOperator = null, $offsetValue = null, bool $includeOffsetValue = null
    ) : \Generator
    {
        $builder = ChunkBuilder::from(static::class);

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
    public static function paginatePdoNativeForeach(
        int $perPage = null, int $page = null, int $pagesCount = null,
        int $offset = null
    ) : \Generator
    {
        $builder = ChunkBuilder::from(static::class);

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
    public static function paginatePdoAfterForeach(
        int $perPage = null, int $page = null, int $pagesCount = null,
        string $offsetColumn = null, string $offsetOperator = null, $offsetValue = null, bool $includeOffsetValue = null
    ) : \Generator
    {
        $builder = ChunkBuilder::from(static::class);

        $builder
            ->fetchPdo()
            ->offsetAfter($offsetColumn, $offsetOperator, $offsetValue, $includeOffsetValue)
            ->yieldPage($perPage, $page, $pagesCount)
        ;

        $generator = $builder->foreach();

        return $generator;
    }
}
