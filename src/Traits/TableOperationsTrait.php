<?php

namespace Fitatu\Cassandra\Traits;

/**
 * @author    Sebastian Szczepański
 * @copyright Fitatu Sp. z o.o.
 */
trait TableOperationsTrait
{
    /**
     * @var null|string
     */
    protected $primaryKey;

    /**
     * @param string $primaryFieldName
     * @param string $type
     * @return TableOperationsTrait
     */
    public function setPrimaryKey(string $primaryFieldName, string $type = 'uuid'): TableOperationsTrait
    {
        $this->query = sprintf(
            $this->query,
            "{$primaryFieldName} {$type} PRIMARY KEY, %s"
        );

        return $this;
    }

    /**
     * @param string $primaryFieldName
     * @return TableOperationsTrait
     */
    public function addPrimaryKey($primaryFieldName): TableOperationsTrait
    {
        $this->primaryKey = "%s, PRIMARY KEY({$primaryFieldName})";

        return $this;
    }

    /**
     * @param string $fieldName
     * @param string $indexName
     * @return \Cassandra\Rows
     */
    public function createIndex(string $fieldName, string $indexName = '')
    {
        $this->query = sprintf(
            "CREATE INDEX %s ON %s (%s)",
            $indexName ?: $fieldName,
            $this->tableName,
            $fieldName
        );

        return $this->persist();
    }

    /**
     * @param string[] $fields
     * @return TableOperationsTrait
     */
    public function withFields(array $fields): TableOperationsTrait
    {
        if ($this->primaryKey) {
            $this->query = sprintf(
                $this->query,
                $this->primaryKey
            );
        }
        $this->query = sprintf(
            $this->query,
            $this->extractFields($fields)
        );

        return $this;
    }

    /**
     * @param string[] $fields
     * @param string   $prefix
     * @return string
     */
    public function extractFields(array $fields, string $prefix = ''): string
    {
        return collect($fields)->map(function ($type, $field) use ($prefix) {
            return $prefix.' '.$field.' '.$type;
        })->implode(', ');
    }

    /**
     * @param string $tableName
     * @return TableOperationsTrait
     */
    public function addTable(string $tableName): TableOperationsTrait
    {
        $this->query = "CREATE TABLE {$tableName}(%s)";

        return $this;
    }

    /**
     * @param string $tableName
     * @return TableOperationsTrait
     */
    public function alterTable(string $tableName): TableOperationsTrait
    {
        $this->query = "ALTER TABLE {$tableName} %s";

        return $this;
    }

    /**
     * @param string $tableName
     * @return \Cassandra\Rows
     */
    public function dropTable(string $tableName)
    {
        $this->query = "DROP TABLE ".$tableName;

        return $this->persist();
    }

    /**
     * @param string $tableName
     * @return \Cassandra\Rows
     */
    public function truncateTable(string $tableName)
    {
        $this->query = "TRUNCATE ".$tableName;

        return $this->persist();
    }
}
