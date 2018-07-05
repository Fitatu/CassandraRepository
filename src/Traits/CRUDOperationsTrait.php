<?php

namespace Fitatu\Cassandra\Traits;

use Fitatu\Cassandra\QueryBuilder;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Collection;

/**
 * @author    Sebastian Szczepański
 * @copyright Fitatu Sp. z o.o.
 */
trait CRUDOperationsTrait
{
    /**
     * @var string
     */
    protected $order = 'ASC';

    /**
     * @var string
     */
    protected $orderBy;

    /**
     * @param array $data
     * @param bool  $timestamp
     * @return string
     */
    public function create(array $data, bool $timestamp = true, bool $withPrimaryKey = true): string
    {
        if ($timestamp) {
            $data['updated_at'] = $this->getCurrentTimestamp();
            $data['created_at'] = $this->getCurrentTimestamp();
        }

        if ($withPrimaryKey) {
            $uuid = $this->getUniquePrimaryKey();
            $data = ['id' => $uuid] + $data;
        }
        $data = collect($data);

        $keys = $data->keys()->implode(', ');
        $values = $this->prepareDataToSave($data);

        $this->query = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->tableName,
            $keys,
            $values
        );

        $this->persist();

        return empty($uuid) ?: $uuid;
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
            if (is_int($value) || Uuid::isValid($value)) {
                return $value;
            }

            $value = addslashes($value);
            if (strpos($value, "\\'") !== false) {
                return sprintf('"%s"', $value);
            }

            return sprintf("'%s'", $value);

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
    public function take(int $limit): QueryBuilder
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @param string $orderBy
     * @param string $order
     * @return QueryBuilder
     */
    public function orderBy(string $orderBy, $order = 'ASC'): QueryBuilder
    {
        $this->orderBy = $orderBy;
        $this->order = $order;

        return $this;
    }

    /**
     * @param array        $criteria
     * @param string|array $fields
     * @return \Cassandra\Rows
     */
    public function findBy(array $criteria, $fields = '*')
    {
        $whereQuery = $this->getConditionsFromArray($criteria, 'AND');

        if ($whereQuery) {
            $whereQuery = "WHERE ".$whereQuery;
        }

        if (is_array($fields)) {
            $fields = implode($fields, ", ");
        }

        if (count($criteria) > 1) {
            $whereQuery .= " ALLOW FILTERING";
        }

        $this->query = sprintf(
            "SELECT %s FROM %s %s %s",
            $fields,
            $this->tableName,
            $whereQuery,
            $this->getQueryParameters()
        );

        return $this->persist();
    }

    /**
     * @return string
     */
    public function getQueryParameters(): string
    {
        $parameters = [];
        if ($this->limit) {
            $parameters[] = 'LIMIT '.(int)$this->limit;
        }

        if ($this->orderBy) {
            $parameters[] = 'ORDER BY '.$this->orderBy.' '.$this->order;
        }

        return implode(" ", $parameters);
    }

    /**
     * @param array  $data
     * @param string $connector
     * @return string
     */
    protected function getConditionsFromArray(array $data, string $connector = ','): string
    {
        return collect($data)->map(function ($value, $key) {
            if (is_array($value)) {
                return sprintf(
                    "%s IN(%s)",
                    $key,
                    implode(', ', $value)
                );
            }

            $pattern = "%s='%s' ";
            if (is_int($value) || Uuid::isValid($value) || is_bool($value)) {
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
