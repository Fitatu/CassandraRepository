<?php

namespace Fitatu\Cassandra\Traits;

use Ramsey\Uuid\Uuid;
use Illuminate\Support\Collection;
use Fitatu\Cassandra\QueryBuilder;

/**
 * @author    Sebastian Szczepański
 * @copyright Fitatu Sp. z o.o.
 */
trait CRUDOperationsTrait
{
    /**
     * @param array $data
     * @param bool  $timestamp
     * @return string
     */
    public function create(array $data, bool $timestamp = true): string
    {
        if ($timestamp) {
            $data['updated_at'] = $this->getCurrentTimestamp();
            $data['created_at'] = $this->getCurrentTimestamp();
        }

        if (is_array($data)) {
            $data = collect($data);
        }
        $keys = $data->keys()->implode(', ');
        $values = $this->prepareDataToSave($data);
        $uuid = $this->getUniquePrimaryKey();
        $this->query = sprintf(
            "INSERT INTO %s (id, %s) VALUES ('%s', %s)",
            $this->tableName,
            $keys,
            $uuid,
            $values
        );

        $this->persist();

        return $uuid;
    }

    /**
     * @return string
     */
    protected function getCurrentTimestamp(): string
    {
        return (new \DateTime())->format('Y-m-d H:i:s');
    }

    /**
     * @param Collection $data
     * @return string
     */
    protected function prepareDataToSave(Collection $data): string
    {
        return $data->values()->map(function ($value) {
            if (is_int($value)) {
                return $value;
            }
            return sprintf("'%s'", addslashes($value));
        })->implode(', ');
    }

    /**
     * @return string
     */
    protected function getUniquePrimaryKey(): string
    {
        return new \Cassandra\Uuid(
            Uuid::uuid4()
        );
    }

    /**
     * @param array  $criteria
     * @param string $fields
     * @return \Cassandra\Row|null;
     */
    public function findOneBy(array $criteria, $fields = '*')
    {
        $this->take(1);

        $results = $this->findBy($criteria, $fields);

        if (!count($results)) {
            return null;
        }
        return $results->first();
    }

    /**
     * @param int $limit
     * @return QueryBuilder
     */
    public function take(int $limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @param array        $criteria
     * @param string|array $fields
     * @return \Cassandra\Rows;
     */
    public function findBy(array $criteria, $fields = '*')
    {
        $criteria = $this->getConditionsFromArray($criteria, 'AND');

        if ($criteria) {
            $criteria = "WHERE ".$criteria;
        }

        if ( strpos($criteria, 'AND') !== false ) {
            $criteria .= " ALLOW FILTERING";
        }

        if (is_array($fields)) {
            $fields = implode($fields, ", ");
        }

        $limit = $this->limit ? 'LIMIT '.(int)$this->limit : '';

        $this->query = sprintf(
            "SELECT %s FROM %s %s %s",
            $fields,
            $this->tableName,
            $criteria,
            $limit
        );
        
        return $this->persist();
    }

    /**
     * @param array  $data
     * @param string $connector
     * @return string
     */
    protected function getConditionsFromArray(array $data, string $connector = ','): string
    {
        return collect($data)->map(function ($value, $key) {
            $pattern = "%s='%s' ";
            if (is_integer($value)) {
                $pattern = "%s=%s ";
            }
            return sprintf($pattern, $key, addslashes($value));
        })->implode($connector.' ');
    }

    /**
     * @param string $fields
     * @return \Cassandra\Rows;
     */
    public function findAll($fields = '*')
    {
        return $this->findBy([], $fields);
    }

    /**
     * @param string $id
     * @return QueryBuilder
     */
    public function withId(string $id): QueryBuilder
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param array $data
     * @param bool  $timestamps
     */
    public function update(array $data, bool $timestamps = true)
    {
        if ($timestamps) {
            $data['updated_at'] = $this->getCurrentTimestamp();
        }

        $data = $this->getConditionsFromArray($data);

        $this->query = sprintf(
            "UPDATE %s SET %s WHERE id='%s'",
            $this->tableName,
            $data,
            $this->id
        );

        $this->persist();
    }

    /**
     * @param string $rowId
     * @param string $field
     * @return \Cassandra\Rows;
     */
    public function deleteFieldFromRow(string $rowId, string $field)
    {
        return $this->delete($rowId, $field);
    }

    /**
     * @param string       $id
     * @param string|array $fields
     * @return \Cassandra\Rows;
     */
    public function delete($id, $fields = '')
    {
        if (is_array($fields)) {
            $fields = implode($fields, ", ");
        }

        $this->query = sprintf(
            "DELETE %s FROM %s WHERE id='%s'",
            $fields,
            $this->tableName,
            $id
        );

        return $this->persist();
    }
}
