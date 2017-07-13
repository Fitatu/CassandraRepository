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
     * @param int             $id
     * @param string          $name
     * @param string          $table
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
     * @param string $entity
     * @param int    $id
     * @param string $name
     * @param string $table
     * @return ArrayCollection
     */
    public function morphMany(string $entity, int $id, string $name, string $table): ArrayCollection
    {
        $ids = $this->cassandra->table($table)->findBy([
            'entity_id'   => $id,
            'entity_type' => $this->getResourceNamespace($entity)
        ]);
        $ids = $this->getRelatedIdsFromIteration($ids, 'media'); //fixme

        $records = $this->findBy(['id' => $ids]);

        return $this->parseRecords($records);
    }

    /**
     * @param \Cassandra\Rows $rows
     * @param string          $name
     * @return string[]
     */
    protected function getRelatedIdsFromIteration($rows, string $name): array
    {
        $fieldName = $name.'_id';

        $ids = collect(iterator_to_array($rows))->map(function ($item) use($fieldName) {
            return $item[$fieldName]->uuid();
        })->toArray();

        if (!$rows->isLastPage()) {
            $ids = $ids + $this->getRelatedIdsFromIteration($rows->nextPage(), $name);
        }

        return $ids;
    }

}
