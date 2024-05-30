<?php

namespace YoRus\DomainExtraLibrary\Infra\Generator;

trait PropertyTrait
{
    const PROPERTY_TYPE_MATCH = [
        'string' => 'string',
        'int' => 'int',
        'float' => 'float',
        'bool' => 'bool',
        'uuid' => 'UuidInterface',
    ];
    /**
     * @param array $properties
     *
     * @return array
     * @throws \Exception
     */
    public function getPrimaryProperty(array $properties): array
    {
        foreach ($properties as $property) {
            if ($property['primary']) {
                return $property;
            }
        }

        throw new \Exception('No primary property found');
    }

    public function getPrimaryPropertyType(array $properties): string
    {
        return $this->getPrimaryProperty($properties)['type'];
    }

    public function getPrimaryPropertyTypeMatch(array $properties): string
    {
        $primaryType = $this->getPrimaryPropertyType($properties);
        if (array_key_exists($primaryType, self::PROPERTY_TYPE_MATCH)) {
            return self::PROPERTY_TYPE_MATCH[$primaryType];
        }

        return $primaryType;
    }
}
