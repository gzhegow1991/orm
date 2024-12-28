<?php

namespace Gzhegow\Database\Core\Model\Traits;

use Gzhegow\Lib\Lib;
use Gzhegow\Database\Core\Orm;
use Gzhegow\Database\Exception\LogicException;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;
use Gzhegow\Database\Package\Illuminate\Database\Schema\EloquentSchemaBuilder;


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

    public static function schema() : EloquentSchemaBuilder
    {
        $model = static::getModel();

        $connection = $model->schemaThis();

        return $connection;
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

        $this->setTableCurrent($table);
    }


    public function getTableCurrent() : ?string
    {
        return $this->table;
    }

    public function setTableCurrent(string $table) : void
    {
        if ('' === $table) {
            throw new LogicException(
                'The `table` should be non-empty string'
            );
        }

        $this->table = $table;
    }


    public function getTablePrefix() : string
    {
        return $this->tablePrefix;
    }

    public function setTablePrefix(string $tablePrefix) : void
    {
        if ('' === $tablePrefix) {
            throw new LogicException(
                'The `tablePrefix` should be non-empty string'
            );
        }

        $this->tablePrefix = $tablePrefix;
    }


    public function getTableNoPrefix() : ?string
    {
        return $this->tableNoPrefix;
    }

    public function setTableNoPrefix(string $tableNoPrefix) : void
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

    public static function tablePrefix() : string
    {
        $model = static::getModel();

        return $model->tablePrefixThis();
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

    private function tableDefaultThis(string $alias = null) : string
    {
        // > gzhegow, Eloquent при подстановке в запрос оборачивает alias согласно Grammar
        // > а вот если пишете RAW запрос, передавайте $alias вместе с кавычками

        $tableDefault = Lib::str()->ends(static::class, 'Model') ?? static::class;
        $tableDefault = class_basename($tableDefault);
        $tableDefault = Lib::str()->snake_lower($tableDefault);

        if ((null !== $alias) && ('' !== $alias)) {
            $tableDefault .= " as {$alias}";
        }

        return $tableDefault;
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

    private function tableMorphedByManyDefaultThis(string $morphTypeName, string $alias = null) : string
    {
        // > gzhegow, Eloquent при подстановке в запрос оборачивает alias согласно Grammar
        // > а вот если пишете RAW запрос, передавайте $alias вместе с кавычками

        $tableDefault = $morphTypeName;

        if ((null !== $alias) && ('' !== $alias)) {
            $tableDefault .= " as {$alias}";
        }

        return $tableDefault;
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
