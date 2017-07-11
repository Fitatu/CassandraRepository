<?php
namespace Fitatu\MediaBundle\Tests\Unit\Cassandra;

use Cassandra\Rows;
use Cassandra\SimpleStatement;
use Fitatu\MediaBundle\Tests\CassandraTestCase;
use Mockery;

/**
 * @author    Sebastian Szczepański
 * @copyright Fitatu Sp. z o.o.
 * @group     unit
 */
class CassandraTableOperationsTest extends CassandraTestCase
{
    const TABLE_NAME = "cass_test_table";
    const TABLE_FIELDS = [
        'field' => 'field_type'
    ];
    
    /**
     * @test
     */
    public function it_sets_primary_key()
    {
        $client = $this->getClient();
        /** @var QueryBuilder $query */
        $query = $this->getCassandra($client);

        $query->addTable(static::TABLE_NAME)
            ->setPrimaryKey('my-id', 'integer');

        $this->setQuery($query);

        $this->assertTrue($this->queryContains('(my-id integer PRIMARY KEY,'));
    }

    /**
     * @test
     */
    public function it_creates_new_table_in_db()
    {
        $client = $this->getClient();

        /** @var QueryBuilder $query */
        $query = $this->getCassandra($client);
        $query->addTable(static::TABLE_NAME)
            ->setPrimaryKey('id')
            ->withFields(static::TABLE_FIELDS);
        $this->setQuery($query);
        $query = sprintf(
            "CREATE TABLE %s(id varchar PRIMARY KEY,  %s)",
            static::TABLE_NAME,
            $this->getImplodedFields()
        );
        
        $this->assertEquals($query, $this->queryString);
    }

    /**
     * @test
     */
    public function it_adds_index_to_existing_table()
    {
        $client = $this->getClient();

        /** @var QueryBuilder $query */
        $query = $this->getCassandra($client);
        $query->table(static::TABLE_NAME)->createIndex('field');
        $this->setQuery($query);
        $query = sprintf(
            "CREATE INDEX field ON %s (field)",
            static::TABLE_NAME
        );
        
        $this->assertEquals($query, $this->queryString);
    }

    /**
     * @test
     */
    public function it_drops_database_table()
    {
        $client = $this->getClient();
        /** @var QueryBuilder $query */
        $query = $this->getCassandra($client);

        $query->dropTable(static::TABLE_NAME);

        $this->setQuery($query);
        $query = sprintf("DROP TABLE %s", static::TABLE_NAME);
        
        $this->assertEquals($query, $this->queryString);
    }

    /**
     * @test
     */
    public function it_truncates_database_table()
    {
        $client = $this->getClient();

        /** @var QueryBuilder $query */
        $query = $this->getCassandra($client);
        
        $query->truncateTable(static::TABLE_NAME);

        $this->setQuery($query);
        $query = sprintf("TRUNCATE %s", static::TABLE_NAME);

        $this->assertEquals($query, $this->queryString);
    }

}
