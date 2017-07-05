<?php

namespace Fitatu\Cassandra\Entity;

use Doctrine\Common\Util\Inflector;
use Illuminate\Support\Collection;


/**
 * @author    Sebastian Szczepański
 * @copyright Fitatu Sp. z o.o.
 */
abstract class AbstractEntity
{
    /**
     * @var string
     */
    protected $id;
    /**
     * @var array
     */
    protected $fillable = [];

    /**
     * @param array $array
     * @return EntityInterface
     */
    public static function create(array $array): EntityInterface
    {
        $item = new static();

        foreach ($array as $key => $value) {
            $methodName = $item->getMethodNameFromKey('set', $key);
            if (method_exists($item, $methodName)) {
                $item->$methodName($value);
            }
        }

        return $item;
    }

    /**
     * @param string $type
     * @param string $key
     * @return string
     */
    public function getMethodNameFromKey(string $type, string $key): string
    {
        return Inflector::camelize($type.ucfirst($key));
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->asCollection()->toArray();
    }

    /**
     * @return Collection
     */
    public function asCollection(): Collection
    {
        $data = [
            'id' => $this->getId()
        ];

        foreach ($this->fillable as $field) {
            $method = $this->getMethodNameFromKey('get', $field);
            $data[$field] = $this->{$method};
        }

        return collect($data);
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return EntityInterface
     */
    public function setId(string $id): EntityInterface
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->asCollection();
    }
}