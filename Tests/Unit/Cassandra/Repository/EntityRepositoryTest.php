<?php

namespace Fitatu\Cassandra\Tests\Cassandra\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Fitatu\Cassandra\Entity\EntityInterface;
use Fitatu\Cassandra\Repository\EntityRepository;
use Fitatu\Cassandra\Tests\CassandraTestCase;
use Fitatu\Cassandra\Tests\Unit\Cassandra\Entity\FakeEntity;
use Mockery;

/**
 * @author    Sebastian Szczepański
 * @copyright Fitatu Sp. z o.o.
 * @group     unit
 */
class EntityRepositoryTest extends CassandraTestCase
{
    const ENTITY_ID = 'test-id';

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getRepository()
    {
        $entity = (new FakeEntity())->setId(static::ENTITY_ID);
        $cassandra = $this->getCassandra(
            $this->getClient()
        );

        return $this->getMockBuilder(EntityRepository::class)->setConstructorArgs([
            $entity, $cassandra
        ])->getMock();
    }

    /**
     * @test
     */
    public function it_can_serch_for_all_items()
    {
        /** @var EntityRepository $repository */
        $repository = $this->getRepository();

        $this->assertTrue(method_exists($repository, 'findAll'));
        $this->assertTrue($repository->findAll() instanceof ArrayCollection);
    }

    /**
     * @test
     */
    public function it_can_search_for_entities_with_given_criteria()
    {
        /** @var EntityRepository $repository */
        $repository = $this->getRepository();
        $this->assertTrue(method_exists($repository, 'findBy'));
        $result = $repository->findBy(['id' => static::ENTITY_ID ]);

        $this->assertTrue($result instanceof ArrayCollection);
    }

    /**
     * @test
     */
    public function it_creates_an_entity()
    {
        $repository = $this->getRepository();

        $repository->method('create')->willReturn('new-id');

        $repository->create([
            'id' => 'new-id'
        ]);
    }

    /**
     * @test
     */
    public function it_can_find_entity_by_given_id()
    {
        $repository = $this->getRepository();
        $this->assertTrue(method_exists($repository, 'find'));
        $this->assertTrue(method_exists($repository, 'findOneBy'));

        $entity = $repository->find(static::ENTITY_ID);
        $this->assertTrue($entity instanceof EntityInterface);
    }

    /**
     * @test
     */
    public function is_updates_an_entity()
    {
        $repository = $this->getRepository();

        $this->assertTrue(method_exists($repository, 'update'));
    }

    /**
     * @test
     */
    public function it_can_transform_records_into_entity()
    {
        $client = $this->getClient();
        $queryBuilder = $this->getCassandra($client);

        $repository = new EntityRepository(
            FakeEntity::class, $queryBuilder
        );
        $this->assertTrue(method_exists($repository, 'transformIntoEntity'));

        $newRecord = $repository->transformIntoEntity([
            'id' => 'test-id'
        ]);

        $this->assertTrue($newRecord instanceof EntityInterface);
        $this->assertEquals('test-id', $newRecord->getId());
    }
}
