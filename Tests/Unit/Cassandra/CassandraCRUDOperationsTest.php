<?php

namespace Fitatu\Cassandra\Tests\Unit\Cassandra;

use Fitatu\Cassandra\QueryBuilder;
use Fitatu\Cassandra\Tests\CassandraTestCase;


/**
 * @author    Sebastian Szczepański
 * @copyright Fitatu Sp. z o.o.
 * @group     unit
 */
class CassandraCRUDOperationsTest extends CassandraTestCase
{
    const ID = 'test-string-id';
    const TABLE_NAME = "cass_test_table";
    const TABLE_FIELDS = [
        'field' => 'field_type'
    ];
    const VALUES = [
        'name' => 'test-name',
        'surname' => 'test-surname',
    ];
    const UUID_PATTERN = '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';

    /**
     * @param string $string
     * @return bool
     */
    private function isUuid(string $string): bool
    {
        return preg_match(static::UUID_PATTERN, $string);
    }
    
    /**
    * @test
    */
    public function it_creates_a_record_in_db()
    {
        $client = $this->getClient();

        /** @var QueryBuilder $query */
        $query = $this->getCassandra($client);
        $result = $query->create(static::VALUES, $createTimeStamps = false);
        $this->setQuery($query);

        foreach (static::VALUES as $key => $value) {
            $this->assertTrue($this->queryContains($key));
            $this->assertTrue($this->queryContains($value));
        }

        $this->assertTrue(is_string($result));
        $this->assertTrue($this->isUuid($result));
        $client->shouldHaveReceived('execute')->once();
        $this->assertTrue($this->queryContains('INSERT INTO'));
        $this->assertFalse($this->queryContains('created_at'));
        $this->assertFalse($this->queryContains('updated_at'));
    }
    
    /**
    * @test
    */
    public function it_creates_a_record_with_timestamps()
    {
        $client = $this->getClient();

        /** @var QueryBuilder $query */
        $query = $this->getCassandra($client);
        $query->create(static::VALUES);
        $this->setQuery($query);

        $client->shouldHaveReceived('execute')->once();
        $this->assertTrue($this->queryContains('created_at'));
        $this->assertTrue($this->queryContains('updated_at'));
        $this->assertTrue($this->queryContains('INSERT INTO'));
    }
    
    /**
    * @test
    */
    public function it_finds_record_by_given_criteria()
    {
        $client = $this->getClient();

        /** @var QueryBuilder $query */
        $query = $this->getCassandra($client);
        $query->findOneBy(static::VALUES);
        $this->setQuery($query);
        $criteria = $this->invokeMethod($query, 'getConditionsFromArray', [static::VALUES, 'AND']);

        $client->shouldHaveReceived('execute')->once();
        $this->assertEquals(1, $this->invokeParameter($query, 'limit'));
        $this->assertTrue($this->queryContains('SELECT * FROM'));
        $this->assertTrue($this->queryContains('WHERE'));
        $this->assertTrue($this->queryContains($criteria));
    }
    
    /**
    * @test
    */
    public function it_takes_limit_parameter()
    {
        $client = $this->getClient();

        /** @var QueryBuilder $query */
        $query = $this->getCassandra($client);
        $query->take(5);

        $this->assertEquals(5, $this->invokeParameter($query, 'limit'));
    }
    
    /**
    * @test
    */
    public function it_finds_records_by_given_criteria()
    {
        $client = $this->getClient();

        /** @var QueryBuilder $query */
        $query = $this->getCassandra($client);
        $query->findBy(static::VALUES);
        $this->setQuery($query);
        $criteria = $this->invokeMethod($query, 'getConditionsFromArray', [static::VALUES, 'AND']);

        $client->shouldHaveReceived('execute')->once();
        $this->assertEmpty($this->invokeParameter($query, 'limit'));
        $this->assertTrue($this->queryContains('SELECT * FROM'));
        $this->assertTrue($this->queryContains('WHERE'));
        $this->assertTrue($this->queryContains($criteria));
    }
    
    /**
    * @test
    */
    public function it_finds_all_records()
    {
        $client = $this->getClient();

        /** @var QueryBuilder $query */
        $query = $this->getCassandra($client);
        $query->findAll();
        $client->shouldHaveReceived('execute')->once();
        $this->setQuery($query);

        $this->assertEmpty($this->invokeParameter($query, 'limit'));
        $this->assertTrue($this->queryContains('SELECT * FROM'));
        $this->assertFalse($this->queryContains('WHERE'));
    }

    /**
    * @test
    */
    public function it_finds_only_selected_fields()
    {
        $client = $this->getClient();

        /** @var QueryBuilder $query */
        $query = $this->getCassandra($client);

        $values = array_keys(static::VALUES);
        $query->findAll($values);
        $client->shouldHaveReceived('execute')->once();
        $this->setQuery($query);
        $values = implode(", ", $values);

        $this->assertTrue($this->queryContains(
            sprintf("SELECT %s FROM", $values)
        ));
    }

    /**
    * @test
    */
    public function it_generates_uuid()
    {
        $client = $this->getClient();

        /** @var QueryBuilder $query */
        $query = $this->getCassandra($client);
        $uuid = $this->invokeMethod($query, 'getUniquePrimaryKey');
        
        $this->assertTrue($this->isUuid($uuid));
    }
    
    /**
    * @test
    */
    public function it_accepts_record_id()
    {
        $client = $this->getClient();

        /** @var QueryBuilder $query */
        $query = $this->getCassandra($client);
        $query->withId('new-string-id');

        $this->assertEquals('new-string-id', $this->invokeParameter($query, 'id'));
    }
    
    /**
    * @test
    */
    public function it_updates_a_row()
    {
        $client = $this->getClient();

        /** @var QueryBuilder $query */
        $query = $this->getCassandra($client);
        $query->update(static::VALUES);
        $this->setQuery($query);
        $criteria = $this->invokeMethod($query, 'getConditionsFromArray', [static::VALUES]);

        $this->assertTrue($this->queryContains('UPDATE'));
        $this->assertTrue($this->queryContains('SET'));
        $this->assertTrue($this->queryContains($criteria));
    }

    /**
    * @test
    */
    public function it_deletes_field_from_row()
    {
        $client = $this->getClient();
        /** @var QueryBuilder $query */
        $query = $this->getCassandra($client);
        $field = array_keys(static::VALUES)[0];
        $query->deleteFieldFromRow(static::ID, $field);
        $this->setQuery($query);

        $firstQuery = $this->queryString;

        $this->assertTrue($this->queryContains(
            sprintf("DELETE %s FROM", $field)
        ));
        $this->assertTrue($this->queryContains(
            sprintf("WHERE id='%s'", static::ID)
        ));

        $query = $this->getCassandra($client);
        $field = array_keys(static::VALUES)[0];
        $query->delete(static::ID, $field);
        $this->setQuery($query);
        
        $this->assertEquals($firstQuery, $this->queryString);
    }

    /**
    * @test
    */
    public function it_deletes_given_row()
    {
        $client = $this->getClient();
        /** @var QueryBuilder $query */
        $query = $this->getCassandra($client);
        $query->delete(static::ID);
        $this->setQuery($query);

        $this->assertTrue($this->queryContains('DELETE  FROM'));
        $this->assertTrue($this->queryContains(
            sprintf("WHERE id='%s'", static::ID)
        ));
    }
}
