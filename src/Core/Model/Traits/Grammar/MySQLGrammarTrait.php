<?php

namespace Gzhegow\Database\Core\Model\Traits\Grammar;

use Gzhegow\Database\Exception\RuntimeException;
use Gzhegow\Database\Package\Illuminate\Database\Eloquent\EloquentModel;


/**
 * @mixin EloquentModel
 */
trait MySQLGrammarTrait
{
    public function getAutoincrementThis() : ?int
    {
        $connection = $this->getConnection();

        $databaseName = $connection->getDatabaseName();
        $tableName = $this->getTable();

        $sql = "
            SELECT `AUTO_INCREMENT`
            FROM INFORMATION_SCHEMA.TABLES
            WHERE
              TABLE_SCHEMA = '{$databaseName}'
              AND TABLE_NAME = '{$tableName}';
        ";
        $rows = $connection->select($sql);

        $autoincrement = null;

        if ($rows) {
            $autoincrement = $rows[ 0 ]->AUTO_INCREMENT;
        }

        return $autoincrement;
    }

    public function setAutoincrementThis(int $autoincrement) : bool
    {
        if ($autoincrement < 1) $autoincrement = 1;

        $tableName = $this->getTable();

        $sql = "
            ALTER TABLE {$tableName}
            AUTO_INCREMENT={$autoincrement};
        ";

        $connection = $this->getConnection();

        $status = $connection->statement($sql);

        return $status;
    }


    public function upsertThis(array $insert, array $update) : bool
    {
        $firstRow = reset($insert) ?: [];
        $columnsToInsert = array_keys($firstRow);

        $connection = $this->getConnection();
        $tableName = $this->getTable();

        $sqlBindings = [];

        $sqlColumns = $columnsToInsert;
        $sqlColumns = implode(',', $sqlColumns);

        $sqlValuesList = [];
        foreach ( $insert as $idx => $row ) {
            $sqlValues = [];
            foreach ( $columnsToInsert as $column ) {
                if (! array_key_exists($column, $row)) {
                    throw new RuntimeException(
                        [
                            'Row is missing column: '
                            . "insert.{$idx}"
                            . " / {$column}",
                        ]
                    );
                }

                $sqlValues[] = '?';
                $sqlBindings[] = $row[ $column ];
            }
            $sqlValues = '(' . implode(',', $sqlValues) . ')';

            $sqlValuesList[] = $sqlValues;
        }
        $sqlValuesList = implode(',', $sqlValuesList);

        $sqlUpdate = [];
        foreach ( $update as $column => $statement ) {
            if (is_int($column)) {
                $column = $statement;
                $statement = (object) [ "VALUES({$column})" ];
            }

            $_statement = null
                ?? (is_object($statement) ? $statement->{'0'} : null)
                ?? (is_scalar($statement) ? '?' : null)
                ?? ((null === $statement) ? '?' : null);

            if (null === $_statement) {
                throw new RuntimeException(
                    [
                        "Each of `update` should be scalar, null, or (object) ['SQL STATEMENT']: update.{$column}",
                        $statement,
                    ]
                );
            }

            if ('?' === $_statement) {
                $sqlBindings[] = $statement;
            }

            $sqlUpdate[] = "`{$column}` = {$_statement}";
        }
        $sqlUpdate = implode(',', $sqlUpdate);

        $sql = "
            INSERT INTO {$tableName} ({$sqlColumns})
            VALUES
            {$sqlValuesList}
            ON DUPLICATE KEY UPDATE
            {$sqlUpdate}
            ;
        ";

        $status = $connection->statement($sql, $sqlBindings);

        return $status;
    }


    public static function getAutoicrement() : ?int
    {
        $model = static::getModel();

        $autoincrement = $model->getAutoincrementThis();

        return $autoincrement;
    }

    public static function setAutoicrement(int $autoincrement) : bool
    {
        $model = static::getModel();

        $status = $model->setAutoincrementThis($autoincrement);

        return $status;
    }


    public static function upsert(array $insert, array $update) : bool
    {
        $model = static::getModel();

        $status = $model->upsertThis($insert, $update);

        return $status;
    }
}
