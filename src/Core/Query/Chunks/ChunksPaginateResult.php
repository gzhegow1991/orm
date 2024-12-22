<?php

namespace Gzhegow\Database\Core\Query\Chunks;

class ChunksPaginateResult
{
    /**
     * @var int|null
     */
    public $totalItems = _UNDEFINED;
    /**
     * @var int|null
     */
    public $totalPages = _UNDEFINED;

    /**
     * @var int
     */
    public $page = _UNDEFINED;
    /**
     * @var int
     */
    public $perPage = _UNDEFINED;
    /**
     * @var int
     */
    public $pagesDelta = _UNDEFINED;

    /**
     * @var int|string
     */
    public $from = _UNDEFINED;
    /**
     * @var int|string
     */
    public $to = _UNDEFINED;

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
