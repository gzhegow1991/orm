<?php

namespace Gzhegow\Database\Core\Model\Traits;

use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;


/**
 * @mixin EloquentModel
 */
trait ColumnsTrait
{
    public function columnsDefault() : array
    {
        return $this->columns ?? [ '#' ];
    }

    public function prepareColumns(array $columns, bool $withTable = null)
    {
        $withTable = $withTable ?? false;

        $table = null;
        if ($withTable) {
            $table = $this->getTable();
        }

        foreach ( $columns as $i => $column ) {
            $isChanged = false;

            if ('#' === $column) {
                $column = $this->getKeyName();
                $isChanged = true;
            }

            if (null !== $table) {
                if (false === strpos($column, '.')) {
                    $column = "{$table}.{$column}";
                    $isChanged = true;
                }
            }

            if ($isChanged) {
                $columns[ $i ] = $column;
            }
        }

        $columns = array_unique($columns);

        return $columns;
    }
}
