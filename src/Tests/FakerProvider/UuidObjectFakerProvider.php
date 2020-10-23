<?php

namespace AppInWeb\DomainExtraLibrary\Tests\FakerProvider;

use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Uuid;

/**
 * Class UuidObjectFakerProvider
 * */
class UuidObjectFakerProvider
{
    /**
     * @param null|string $uuidString
     *
     * @return UuidInterface
     *
     * @throws \Exception
     */
    public static function uuidObject(string $uuidString = null): UuidInterface
    {
        if (null === $uuidString) {
            return Uuid::uuid4();
        }

        return Uuid::fromString($uuidString);
    }
}
