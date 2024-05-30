<?php

namespace YoRus\DomainExtraLibrary\Domain\Generator;

interface GeneratorInterface
{
    public function generate(string $context, string $entityName, array $fields);
    public function setDryRun(bool $dryRun): void;
    public function addAction(string $action): void;
    public function getActions(): array;
    public function setNamespace(string $namespace): void;
}