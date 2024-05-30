<?php

namespace YoRus\DomainExtraLibrary\Domain\Repository;

use YoRus\DomainExtraLibrary\Domain\Exception\ResourceNotFoundException;
use Ramsey\Uuid\UuidInterface;

/**
 * Interface RequiredFinderRepositoryInterface
 */
interface RequiredFinderRepositoryInterface
{
    /**
     * @param UuidInterface $uuid id
     *
     * @return mixed
     *
     * @throws ResourceNotFoundException
     */
    public function findRequired(UuidInterface $uuid): mixed;
}
