<?php

namespace Fitatu\MediaBundle\Tests\Unit\Cassandra\Entity;

use Fitatu\Cassandra\Tests\Unit\Cassandra\Entity\FakeEntity;

/**
 * @author    Sebastian Szczepański
 * @copyright Fitatu Sp. z o.o.
 * @group unit           
 */
class EntityTest extends \PHPUnit_Framework_TestCase
{
    const ENTITY_ID = 'test-id';
    /**
     * @return FakeEntity
     */
    private function getEntity(): FakeEntity
    {
        return (new FakeEntity())
            ->setId(static::ENTITY_ID);
    }
    /**
    * @test
    */
    public function it_has_given_id()
    {
        $this->assertEquals(static::ENTITY_ID, $this->getEntity()->getId());
    }
    
    /**
    * @test
    */
    public function it_can_be_casted_as_an_array()
    {
        $entity = $this->getEntity();
        
        $this->assertTrue(method_exists($entity, 'toArray'));
        $this->assertTrue(is_array($entity->toArray()));
    }
    
    /**
    * @test
    */
    public function it_can_be_casted_as_a_string()
    {
        $entity = $this->getEntity();

        $this->assertTrue(method_exists($entity, '__toString'));
        $this->assertTrue(is_string((string)$entity));
    }
    /**
    * @test
    */
    public function it_has_static_create_method()
    {
        $entity = $this->getEntity();
        $reflector = new \ReflectionMethod($entity, 'create');
        $this->assertTrue($reflector->isStatic());
    }
}
