<?php

namespace YoRus\DomainExtraLibrary\Infra\Trait;

use InvalidArgumentException;

/**
 * Class SearchableQueryTrait
 */
trait SearchableQueryTrait
{
    /**
     * @var string[]
     */
    protected array $managedProperties = [];

    /**
     * @var mixed[]
     */
    protected array $payload;

    /**
     * @var bool
     */
    protected bool $hasParameters = false;

    /**
     * @param string $property
     *
     * @return null|mixed
     *
     * @throws InvalidArgumentException
     */
    public function getProperty(string $property): mixed
    {
        if (property_exists($this, $property)) {
            return $this->{$property};
        }

        throw new InvalidArgumentException(
            sprintf(
                'No property `%s` defined in `%s` class',
                $property,
                get_class($this)
            )
        );
    }

    /**
     * @return bool
     */
    public function hasParameters(): bool
    {
        return $this->hasParameters;
    }

    /**
     * Hydrate managed properties with query parameters defined in the payload
     */
    protected function setProperties(): void
    {
        foreach ($this->managedProperties as $property) {
            if (array_key_exists($property, $this->payload) && property_exists($this, $property)) {
                $this->{$property} = $this->payload[$property];
                $this->hasParameters = true;
            }
        }
    }
}
