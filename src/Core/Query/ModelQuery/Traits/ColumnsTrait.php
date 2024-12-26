<?php

namespace Gzhegow\Database\Core\Query\ModelQuery\Traits;

use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModelQueryBuilder;


/**
 * @mixin EloquentModelQueryBuilder
 */
trait ColumnsTrait
{
    /**
     * @var string[]
     */
    protected $columnsDefaultAppend = [];


    /**
     * @return static
     */
    public function resetColumns(array $columnsDefault) // : static
    {
        $this->columnsDefaultAppend = [];

        $this->addColumns($columnsDefault);

        return $this;
    }

    /**
     * @return static
     */
    public function addColumns(array $columnsDefault) // : static
    {
        foreach ( $columnsDefault as $column ) {
            $this->addColumn($column);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function addColumn(string $column) // : static
    {
        $this->columnsDefaultAppend[] = $column;

        return $this;
    }
}
