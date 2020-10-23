<?php

namespace AppInWeb\DomainExtraLibrary\Infra\Validator\Constraint;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * ResourceMustExistConstraint
 */
class ResourceMustExistConstraint extends Constraint
{
    /** @var string */
    public $messageShouldExists = '{{ resource }} with identifier `{{ id }}` does not exist.';

    /**
     * @var string
     */
    public $reader;

    /**
     * @var string
     */
    public $resource = 'Resource';

    /**
     * @var int
     */
    public $code = Response::HTTP_NOT_FOUND;

    /**
     * {@inheritdoc}
     */
    public function getDefaultOption(): string
    {
        return 'reader';
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        if (null === $this->reader) {
            throw new InvalidArgumentException('The `reader` option is mandatory to specify the validator service');
        }

        return $this->reader;
    }
}
