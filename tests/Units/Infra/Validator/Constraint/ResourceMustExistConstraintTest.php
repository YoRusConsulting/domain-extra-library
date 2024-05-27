<?php

namespace YoRus\DomainExtraLibrary\Tests\Units\Infra\Validator\Constraint;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Exception\InvalidArgumentException;
use YoRus\DomainExtraLibrary\Infra\Validator\Constraint\ResourceMustExistConstraint;

class ResourceMustExistConstraintTest extends TestCase
{
    public function testGetDefaultOption()
    {
        $constraint = new ResourceMustExistConstraint();
        $this->assertEquals('reader', $constraint->getDefaultOption());
    }

    public function testValidatedBy()
    {
        $constraint = new ResourceMustExistConstraint();
        $constraint->reader = 'reader';
        $this->assertEquals('reader', $constraint->validatedBy());
    }

    public function testValidatedByThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The `reader` option is mandatory to specify the validator service');
        $constraint = new ResourceMustExistConstraint();
        $constraint->validatedBy();
    }
}