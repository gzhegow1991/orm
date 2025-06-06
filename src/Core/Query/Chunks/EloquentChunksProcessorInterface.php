<?php

namespace Gzhegow\Orm\Core\Query\Chunks;

use Illuminate\Support\Collection as EloquentSupportCollection;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\Base\EloquentModel;
use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelCollection;


/**
 * @template-covariant T of EloquentModel
 */
interface EloquentChunksProcessorInterface
{
    /**
     * @return \Generator<EloquentModelCollection<T>>
     */
    public function chunksModelNativeForeach(EloquentChunksBuilder $builder) : \Generator;

    /**
     * @return \Generator<EloquentSupportCollection<\stdClass>
     */
    public function chunksPdoNativeForeach(EloquentChunksBuilder $builder) : \Generator;


    /**
     * @return \Generator<EloquentModelCollection<T>>
     */
    public function chunksModelAfterForeach(EloquentChunksBuilder $builder) : \Generator;

    /**
     * @return \Generator<EloquentSupportCollection<\stdClass>
     */
    public function chunksPdoAfterForeach(EloquentChunksBuilder $builder) : \Generator;


    public function paginateModelNativeForeach(EloquentChunksBuilder $builder) : EloquentChunksPaginateResult;

    public function paginatePdoNativeForeach(EloquentChunksBuilder $builder) : EloquentChunksPaginateResult;


    public function paginateModelAfterForeach(EloquentChunksBuilder $builder) : EloquentChunksPaginateResult;

    public function paginatePdoAfterForeach(EloquentChunksBuilder $builder) : EloquentChunksPaginateResult;
}
