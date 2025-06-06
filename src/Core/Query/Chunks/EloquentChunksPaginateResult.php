<?php

namespace Gzhegow\Orm\Core\Query\Chunks;


class EloquentChunksPaginateResult
{
    /**
     * @var int|null
     */
    public $totalItems = [];
    /**
     * @var int|null
     */
    public $totalPages = [];

    /**
     * @var int
     */
    public $page = [];
    /**
     * @var int
     */
    public $perPage = [];
    /**
     * @var int
     */
    public $pagesDelta = [];

    /**
     * @var int|string
     */
    public $from = [];
    /**
     * @var int|string
     */
    public $to = [];

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
