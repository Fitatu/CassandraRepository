<?php

namespace Fitatu\MediaBundle\Cassandra\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Fitatu\MediaBundle\Cassandra\Entity\EntityInterface;

/**
 * @author    Sebastian Szczepański
 * @copyright Fitatu Sp. z o.o.
 */
interface ObjectRepository
{
    /**
     * @param string $id The identifier.
     *
     * @return EntityInterface
     */
    public function find(string $id): EntityInterface;

    /**
     * @return ArrayCollection
     */
    public function findAll(): ArrayCollection;

    /**
     * @param array      $criteria
     * @param int|null   $limit
     *
     * @return ArrayCollection
     */
    public function findBy(array $criteria, $limit = null): ArrayCollection;

    /**
     * @param array $criteria The criteria.
     * @param string|array $fields
     *
     * @return EntityInterface|array
     */
    public function findOneBy(array $criteria, $fields);

    /**
     * @return string
     */
    public function getClassName(): string;
}