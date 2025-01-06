<?php

namespace Gzhegow\Database\Core\Query\Chunks;

use Gzhegow\Lib\Modules\BoolModule;


class ChunksPaginateResult
{
    /**
     * @var int|null
     */
    public $totalItems = BoolModule::UNDEFINED;
    /**
     * @var int|null
     */
    public $totalPages = BoolModule::UNDEFINED;

    /**
     * @var int
     */
    public $page = BoolModule::UNDEFINED;
    /**
     * @var int
     */
    public $perPage = BoolModule::UNDEFINED;
    /**
     * @var int
     */
    public $pagesDelta = BoolModule::UNDEFINED;

    /**
     * @var int|string
     */
    public $from = BoolModule::UNDEFINED;
    /**
     * @var int|string
     */
    public $to = BoolModule::UNDEFINED;

    /**
     * @var array<int|null>
     */
    public $pagesAbsolute = [];
    /**
     * @var array{
     *     first: int|null,
     *     previous: int|null,
     *     current: int|null,
     *     next: int|null,
     *     last: int|null,
     * }
     */
    public $pagesRelative = [
        'first'    => null,
        'previous' => null,
        'current'  => null,
        'next'     => null,
        'last'     => null,
    ];

    /**
     * @var array
     */
    public $items = [];
}
