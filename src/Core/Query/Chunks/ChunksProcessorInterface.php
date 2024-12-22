<?php

namespace Gzhegow\Database\Core\Query\Chunks;

use Illuminate\Support\Collection as EloquentSupportCollection;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelCollection;


/**
 * @template-covariant T of EloquentModel
 */
interface ChunksProcessorInterface
{
    /**
     * @return \Generator<EloquentModelCollection<T>>
     */
    public function chunksModelNativeForeach(ChunksBuilder $builder) : \Generator;

    /**
     * @return \Generator<EloquentSupportCollection<\stdClass>
     */
    public function chunksPdoNativeForeach(ChunksBuilder $builder) : \Generator;


    /**
     * @return \Generator<EloquentModelCollection<T>>
     */
    public function chunksModelAfterForeach(ChunksBuilder $builder) : \Generator;

    /**
     * @return \Generator<EloquentSupportCollection<\stdClass>
     */
    public function chunksPdoAfterForeach(ChunksBuilder $builder) : \Generator;


    public function paginateModelNativeForeach(ChunksBuilder $builder) : ChunksPaginateResult;

    public function paginatePdoNativeForeach(ChunksBuilder $builder) : ChunksPaginateResult;


    public function paginateModelAfterForeach(ChunksBuilder $builder) : ChunksPaginateResult;

    public function paginatePdoAfterForeach(ChunksBuilder $builder) : ChunksPaginateResult;
}
