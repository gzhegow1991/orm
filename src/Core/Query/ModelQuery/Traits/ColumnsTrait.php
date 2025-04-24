<?php

namespace Gzhegow\Orm\Core\Query\ModelQuery\Traits;

use Gzhegow\Orm\Package\Illuminate\Database\Eloquent\EloquentModelQueryBuilder;


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
    public function resetColumns(array $columnsDefault)
    {
        $this->columnsDefaultAppend = [];

        $this->addColumns($columnsDefault);

        return $this;
    }

    /**
     * @return static
     */
    public function addColumns(array $columnsDefault)
    {
        foreach ( $columnsDefault as $column ) {
            $this->addColumn($column);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function addColumn(string $column)
    {
        $this->columnsDefaultAppend[] = $column;

        return $this;
    }
}
