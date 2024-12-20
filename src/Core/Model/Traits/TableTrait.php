<?php

namespace Gzhegow\Database\Core\Model\Traits;

use Gzhegow\Lib\Lib;
use Gzhegow\Database\Core\Orm;
use Gzhegow\Database\Exception\LogicException;
use Illuminate\Database\Schema\Builder as EloquentSchemaBuilder;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;


/**
 * @mixin EloquentModel
 */
trait TableTrait
{
    public function schemaThis() : EloquentSchemaBuilder
    {
        $connection = $this->getConnection();

        $schema = Orm::newEloquentSchemaBuilder($connection);

        return $schema;
    }


    public function getTable()
    {
        /** @see Model::getTable() */

        $table = $this->tableThis();

        return $table;
    }

    public function setTable($table)
    {
        /** @see Model::setTable() */

        $this->setTableThis($table);
    }


    public function getTableThis() : ?string
    {
        return $this->table;
    }

    public function setTableThis(string $table) : void
    {
        if ('' === $table) {
            throw new LogicException(
                'The `table` should be non-empty string'
            );
        }

        $this->table = $table;
    }


    public function getTablePrefixThis() : string
    {
        return $this->tablePrefix;
    }

    public function setTablePrefixThis(string $tablePrefix) : void
    {
        if ('' === $tablePrefix) {
            throw new LogicException(
                'The `tablePrefix` should be non-empty string'
            );
        }

        $this->tablePrefix = $tablePrefix;
    }


    public function getTableNoPrefixThis() : ?string
    {
        return $this->tableNoPrefix;
    }

    public function setTableNoPrefixThis(string $tableNoPrefix) : void
    {
        if ('' === $tableNoPrefix) {
            throw new LogicException(
                'The `tableNoPrefix` should be non-empty string'
            );
        }

        $this->tableNoPrefix = $tableNoPrefix;
    }


    public function tablePrefixThis() : ?string
    {
        return $this->tablePrefix;
    }


    public function tableThis(string $alias = null) : string
    {
        // > gzhegow, Eloquent при подстановке в запрос оборачивает alias согласно Grammar
        // > а вот если пишете RAW запрос, передавайте $alias вместе с кавычками

        $table =
            $this->table
            ?? ($this->tableNoPrefix ? ($this->tablePrefix . $this->tableNoPrefixThis()) : null)
            ?? ($this->tablePrefix . $this->tableDefaultThis());

        if ((null !== $alias) && ('' !== $alias)) {
            $table .= " as {$alias}";
        }

        return $table;
    }

    public function tableNoPrefixThis(string $alias = null) : string
    {
        // > gzhegow, Eloquent при подстановке в запрос оборачивает alias согласно Grammar
        // > а вот если пишете RAW запрос, передавайте $alias вместе с кавычками

        $tableNoPrefix =
            $this->tableNoPrefix
            ?? $this->tableDefaultThis();

        if ((null !== $alias) && ('' !== $alias)) {
            $tableNoPrefix .= " as {$alias}";
        }

        return $tableNoPrefix;
    }

    protected function tableDefaultThis(string $alias = null) : string
    {
        // > gzhegow, Eloquent при подстановке в запрос оборачивает alias согласно Grammar
        // > а вот если пишете RAW запрос, передавайте $alias вместе с кавычками

        $tableDefault = Lib::str_ends(static::class, 'Model') ?? static::class;
        $tableDefault = class_basename($tableDefault);
        $tableDefault = Lib::str_snake_lower($tableDefault);

        if ((null !== $alias) && ('' !== $alias)) {
            $tableDefault .= " as {$alias}";
        }

        return $tableDefault;
    }


    public function tableMorphedByManyThis(string $morphTypeName, string $alias = null) : string
    {
        // > gzhegow, Eloquent при подстановке в запрос оборачивает alias согласно Grammar
        // > а вот если пишете RAW запрос, передавайте $alias вместе с кавычками

        $table = $this->tablePrefix . $this->tableMorphedByManyDefaultThis($morphTypeName);

        if ((null !== $alias) && ('' !== $alias)) {
            $table .= " as {$alias}";
        }

        return $table;
    }

    public function tableMorphedByManyNoPrefixThis(string $morphTypeName, string $alias = null) : string
    {
        // > gzhegow, Eloquent при подстановке в запрос оборачивает alias согласно Grammar
        // > а вот если пишете RAW запрос, передавайте $alias вместе с кавычками

        $tableNoPrefix = $this->tableMorphedByManyDefaultThis($morphTypeName);

        if ((null !== $alias) && ('' !== $alias)) {
            $tableNoPrefix .= " as {$alias}";
        }

        return $tableNoPrefix;
    }

    protected function tableMorphedByManyDefaultThis(string $morphTypeName, string $alias = null) : string
    {
        // > gzhegow, Eloquent при подстановке в запрос оборачивает alias согласно Grammar
        // > а вот если пишете RAW запрос, передавайте $alias вместе с кавычками

        $tableDefault = $morphTypeName;

        if ((null !== $alias) && ('' !== $alias)) {
            $tableDefault .= " as {$alias}";
        }

        return $tableDefault;
    }


    public static function schema() : EloquentSchemaBuilder
    {
        $model = static::getModel();

        $connection = $model->schemaThis();

        return $connection;
    }


    public static function tablePrefix() : string
    {
        $model = static::getModel();

        return $model->tablePrefixThis();
    }


    public static function table(string $alias = null) : string
    {
        $model = static::getModel();

        return $model->tableThis($alias);
    }

    public static function tableNoPrefix(string $alias = null) : string
    {
        $model = static::getModel();

        return $model->tableNoPrefixThis($alias);
    }


    public static function tableMorphedByMany(string $morphTypeName, string $alias = null) : string
    {
        $model = static::getModel();

        return $model->tableMorphedByManyThis($morphTypeName, $alias);
    }

    public static function tableNoPrefixMorphedByMany(string $morphTypeName, string $alias = null) : string
    {
        $model = static::getModel();

        return $model->tableMorphedByManyNoPrefixThis($morphTypeName, $alias);
    }
}
