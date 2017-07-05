<?php

namespace Fitatu\Cassandra\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Fitatu\Cassandra\Entity\EntityInterface;

/**
 * @author    Sebastian Szczepański
 * @copyright Fitatu Sp. z o.o.
 */
trait RelationshipsTrait
{
    /**
     * @param EntityInterface $entity
     * @param int    $id
     * @param string $name
     * @param string $table
     * @return ArrayCollection
     */
    public function morphedByMany($entity, int $id, string $name, string $table): ArrayCollection
    {
        /** @var ArrayCollection $ids */
        $ids = $this->cassandra->table($table)
            ->findBy([
                $name.'_id'   => $id,
                $name.'_type' => $this->getResourceNamespace($entity)
            ]);
        // todo
    }

    /**
     * @param string  $entity
     * @param int    $id
     * @param string $name
     * @param string $table
     * @return ArrayCollection
     */
    public function morphMany(string $entity, int $id, string $name, string $table): ArrayCollection
    {
        $ids = $this->cassandra->table($table)->findBy([
            'entity_id' => $id,
            'entity_type'            => $this->getResourceNamespace(stripslashes($entity))
        ]);
        $ids = collect((array)$ids);
        
        $records = [];

        $ids->map(function ($row) use ($records) {
            $records[] = $this->repository()->find($row->{$name.'_id'});
        });

        return $this->parseRecords($records);
    }
}
