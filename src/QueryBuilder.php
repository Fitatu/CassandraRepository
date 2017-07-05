<?php

namespace Fitatu\MediaBundle\Cassandra;

use Cassandra\SimpleStatement;
use Cassandra\Session as Client;
use Fitatu\MediaBundle\Cassandra\Traits\TableOperationsTrait;
use Fitatu\MediaBundle\Cassandra\Traits\CRUDOperationsTrait;

/**
 * @author    Sebastian Szczepański
 * @copyright Fitatu Sp. z o.o.
 */
class QueryBuilder
{
    use TableOperationsTrait;
    use CRUDOperationsTrait;

    /**
     * @var Client
     */
    private $cassandra;

    /**
     * @var string
     */
    private $query = '';

    /**
     * @var string[]
     */
    private $fields = [];

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string
     */
    private $id;

    /**
     * @var int
     */
    private $limit;

    /**
     * @param Client $cassandra
     */
    public function __construct(Client $cassandra)
    {
        $this->cassandra = $cassandra;
    }
    
    /**
     * @param string $tableName
     * @return QueryBuilder
     */
    public function table(string $tableName): QueryBuilder
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * @return Client
     */
    public function client(): Client
    {
        return $this->cassandra;
    }
    
    /**
     * @return mixed
     */
    public function persist()
    {
        return $this->cassandra->execute(
            new SimpleStatement($this->query)
        );
    }
}
