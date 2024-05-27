<?php

namespace YoRus\DomainExtraLibrary\Infra\Validator\Constraint;

use YoRus\DomainExtraLibrary\Domain\EventListener\ExceptionListener;
use YoRus\DomainExtraLibrary\Domain\Exception\ResourceNotFoundException;
use YoRus\DomainExtraLibrary\Infra\Repository\RequiredFinderRepositoryInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * ResourceMustExistConstraintValidator
 */
class ResourceMustExistConstraintValidator extends ConstraintValidator
{
    /**
     * @var RequiredFinderRepositoryInterface
     */
    private $reader;

    /**
     * ResourceMustExistConstraintValidator constructor.
     *
     * @param RequiredFinderRepositoryInterface $reader
     */
    public function __construct(RequiredFinderRepositoryInterface $reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ResourceMustExistConstraint) {
            throw new UnexpectedTypeException($constraint, ResourceMustExistConstraint::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if (!Uuid::isValid($value)) {
            $this->context->buildViolation($constraint->messageShouldExists)
                ->setParameter('{{ id }}', $value)
                ->setParameter('{{ resource }}', $constraint->resource)
                ->setCode(ExceptionListener::HTTP_CONTEXT_PREFIX . $constraint->code)
                ->addViolation();

            // No need to continue the validation, the uuid is not correct and will crash the process
            return;
        }

        try {
            // Resource must exist
            $this->reader->findRequired(Uuid::fromString($value));
        } catch (ResourceNotFoundException $e) {
            $this->context->buildViolation($constraint->messageShouldExists)
                ->setParameter('{{ id }}', $value)
                ->setParameter('{{ resource }}', $constraint->resource)
                ->setCode(ExceptionListener::HTTP_CONTEXT_PREFIX . $constraint->code)
                ->addViolation();
        }
    }
}
