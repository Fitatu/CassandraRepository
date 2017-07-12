<?php

namespace Fitatu\Cassandra\Tests;

use Fitatu\Cassandra\QueryBuilder;
use M6Web\Bundle\CassandraBundle\Cassandra\Client;
use Mockery;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase;

/**
 * @author    Sebastian Szczepański
 * @copyright Fitatu Sp. z o.o.
 */
class CassandraTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $queryString;

    /**
     * @param MockInterface|null $client
     * @return QueryBuilder
     */
    public function getCassandra($client = null): QueryBuilder
    {
        if (is_null($client)) {
            $client = $this->getClient();
        }

        return new QueryBuilder($client);
    }

    /**
     * @return MockInterface
     */
    public function getClient(): MockInterface
    {
        return Mockery::spy(Client::class);
    }

    /**
     * @param string $separator
     * @return string
     */
    public function getImplodedFields($separator = " "): string
    {
        return str_replace("=", $separator, http_build_query(static::TABLE_FIELDS));
    }

    /**
     * @param object $object
     * @param string $methodName
     * @param array  $parameters
     * @return mixed
     */
    public function invokeMethod(&$object, string $methodName, array $parameters = [])
    {
        $method = $this->getReflection($object)->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * @param object $object
     * @param string $key
     * @return \ReflectionProperty
     */
    public function invokeParameter(&$object, $key)
    {
        $property = $this->getReflection($object)->getProperty($key);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    /**
     * @param object $object
     * @return \ReflectionClass
     */
    private function getReflection(&$object): \ReflectionClass
    {
        return new \ReflectionClass(get_class($object));
    }

    /**
     * @param QueryBuilder $query
     */
    public function setQuery(QueryBuilder $query)
    {
        $this->queryString = $this->invokeParameter($query, 'query');
    }

    /**
     * @param QueryBuilder $query
     * @param string       $string
     * @return bool
     */
    public function queryContains(string $string, QueryBuilder $query = null): bool
    {
        if (!empty($query)) {
            $this->queryString = $this->invokeParameter($query, 'query');
        }

        return strpos($this->queryString, $string) !== false;
    }
}

