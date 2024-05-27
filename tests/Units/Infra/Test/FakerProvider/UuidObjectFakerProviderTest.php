<?php

namespace YoRus\DomainExtraLibrary\Tests\Units\Infra\Test\FakerProvider;

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use YoRus\DomainExtraLibrary\Infra\Test\FakerProvider\UuidObjectFakerProvider;

class UuidObjectFakerProviderTest extends TestCase
{
    public function testUuidObject(): void
    {
        $this->assertInstanceOf(UuidInterface::class, UuidObjectFakerProvider::uuidObject());
    }

    public function testUuidObjectWithUuidString(): void
    {
        $uuidString = 'ef0b0e2f-86e5-4c3d-9983-a21d043243cf';
        $this->assertInstanceOf(UuidInterface::class, UuidObjectFakerProvider::uuidObject($uuidString));
        $this->assertEquals($uuidString, UuidObjectFakerProvider::uuidObject($uuidString)->toString());
    }
}