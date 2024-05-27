<?php

namespace YoRus\DomainExtraLibrary\Infra\Validator\Constraint;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * ResourceMustExistConstraint
 */
class ResourceMustExistConstraint extends Constraint
{
    /** @var string */
    public string $messageShouldExists = '{{ resource }} with identifier `{{ id }}` does not exist.';

    /**
     * @var string|null
     */
    public ?string $reader = null;

    /**
     * @var string
     */
    public string $resource = 'Resource';

    /**
     * @var int
     */
    public int $code = Response::HTTP_NOT_FOUND;

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
        if (!$this->reader) {
            throw new InvalidArgumentException('The `reader` option is mandatory to specify the validator service');
        }

        return $this->reader;
    }
}
