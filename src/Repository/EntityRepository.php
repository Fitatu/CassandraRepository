<?php

namespace Fitatu\Cassandra\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Fitatu\Cassandra\Entity\AbstractEntity;
use Fitatu\Cassandra\Entity\EntityInterface;
use Fitatu\Cassandra\QueryBuilder;
use Fitatu\Cassandra\Traits\RelationshipsTrait;

/**
 * @author    Sebastian Szczepański
 * @copyright Fitatu Sp. z o.o.
 */
class EntityRepository implements ObjectRepository
{
    use RelationshipsTrait;
    /**
     * @var string
     */
    protected $entityName;
    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var QueryBuilder
     */
    protected $cassandra;

    /**
     * @var bool
     */
    protected $timestamps = true;

    /**
     * @param EntityManager $em
     * @param QueryBuilder  $cassandra
     */
    public function __construct($em, QueryBuilder $cassandra)
    {
        $this->entityName = (string)$em;
        $this->em = $em;
        $this->cassandra = $cassandra;
    }

    /**
     * @return ArrayCollection
     */
    public function findAll(): ArrayCollection
    {
        return $this->parseRecords(
            $this->repository()->findAll()
        );
    }

    /**
     * @param mixed $records
     * @return ArrayCollection
     */
    public function parseRecords($records): ArrayCollection
    {
        $result = [];
        foreach ($records as $record) {
            $result[] = $record;
        }

        $result = new ArrayCollection($result);

        if ($this->canTransformIntoEntity()) {
            return $result->map(function ($entity) {
                return $this->transformIntoEntity($entity);
            });
        }
        return $result;
    }

    /**
     * @return bool
     */
    protected function canTransformIntoEntity()
    {
        return method_exists($this->entityName, 'create') && (new \ReflectionMethod($this->getClassName(), 'create'))->isStatic();
    }

    /**
     * Returns the class name of the object managed by the repository.
     *
     * @return string
     */
    public function getClassName(): string
    {
        return $this->entityName;
    }

    /**
     * @param array $data
     * @return EntityInterface
     */
    public function transformIntoEntity(array $data)
    {
        /** @var AbstractEntity $entity */
        $entity = $this->entityName;

        return $entity::create($data);
    }

    /**
     * @return QueryBuilder
     */
    public function repository(): QueryBuilder
    {
        return $this->cassandra->table(static::TABLE_NAME);
    }

    /**
     * @param array $criteria
     * @param int  $limit
     * @return ArrayCollection
     */
    public function findBy(array $criteria, $limit = 100): ArrayCollection
    {
        return new ArrayCollection(
            iterator_to_array($this->repository()->take($limit)->findBy($criteria))
        );
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->find(
            $this->repository()->create($data, $this->timestamps)
        );
    }

    /**
     * @param string $id
     * @return EntityInterface
     */
    public function find(string $id): EntityInterface
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * @param array  $criteria
     * @param string $fields
     * @return EntityInterface|array
     */
    public function findOneBy(array $criteria, $fields = '*')
    {
        $data = $this->repository()->findOneBy($criteria, $fields);
        if ($this->canTransformIntoEntity()) {
            return $this->transformIntoEntity($data);
        }

        return $data;
    }

    /**
     * @param EntityInterface $entity
     * @return EntityInterface
     */
    public function update(EntityInterface $entity): EntityInterface
    {
        return $this->repository()
            ->withId($entity->getId())
            ->update($entity->toArray(), $this->timestamps);
    }

    /**
     * @param mixed $resource
     * @return string
     */
    public function getResourceNamespace($resource): string
    {
        return (new \ReflectionClass($resource))->getName();
    }
}
