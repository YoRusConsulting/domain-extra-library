<?php

namespace AppInWeb\DomainExtraLibrary\Infra\Traits;

use AppInWeb\Bundle\EAVBundle\Mapper\Mapper;
use stdClass as Schema;
use stdClass as Structure;

trait EAVTrait
{
    /** @var Mapper */
    private $mapper;

    /**
     * @param Mapper $mapper
     */
    public function setEAVMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * @param string $class
     *
     * @return Schema
     */
    public function getAttributes(string $class): Schema
    {
        return $this->mapper->getAttributes($this, $class);
    }

    /**
     * @param string    $class
     * @param Structure $attributes
     *
     * @throws \App\Bundle\EAVBundle\Mapper\MapperException
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \ReflectionException
     */
    public function setAttributes(string $class, Schema $attributes): void
    {
        $this->mapper->setAttributes($this, $class, $attributes);
    }

    /**
     * @param string    $class
     * @param Structure $values
     */
    public function setValues(string $class, $values): void
    {
        $this->mapper->setValues($this, $class,  $values);
    }

    /**
     * @param string $class
     *
     * @return Structure
     */
    public function getValues(string $class): Structure
    {
        return $this->mapper->getValues($this, $class);
    }
}
