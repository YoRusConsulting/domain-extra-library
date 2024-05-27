<?php

namespace YoRus\DomainExtraLibrary\Infra\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

/**
 * JsonbType
 */
class JsonbType extends Type
{
    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getJsonTypeDeclarationSQL($column);
    }

    /**
     * {@inheritdoc}
     *
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        if (null === $value) {
            return null;
        }

        $encoded = json_encode($value);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw ConversionException::conversionFailedSerialization($value, 'json', json_last_error_msg());
        }

        return $encoded;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (\is_resource($value)) {
            $value = stream_get_contents($value);
        }

        $val = json_decode($value);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }

        return $val;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'jsonb';
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return !$platform->hasDoctrineTypeMappingFor('json');
    }
}
