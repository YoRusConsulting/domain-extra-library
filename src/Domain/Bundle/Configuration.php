<?php

namespace YoRus\DomainExtraLibrary\Domain\Bundle;

class Configuration
{
    public function __construct(
        private readonly string $namespace
    )
    {
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }
}
