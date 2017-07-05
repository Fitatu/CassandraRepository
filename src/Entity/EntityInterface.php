<?php

namespace Fitatu\Cassandra\Entity;

/**
 * @author    Sebastian Szczepański
 * @copyright Fitatu Sp. z o.o.
 */
interface EntityInterface
{
    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @param string $id
     * @return EntityInterface
     */
    public function setId(string $id): EntityInterface;

    /**
     * @return array
     */
    public function toArray(): array;

    /**
     * @return string
     */
    public function __toString(): string;
}