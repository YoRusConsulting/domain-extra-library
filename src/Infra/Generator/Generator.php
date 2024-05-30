<?php

namespace YoRus\DomainExtraLibrary\Infra\Generator;

use YoRus\DomainExtraLibrary\Domain\Generator\GeneratorInterface;

class Generator implements GeneratorInterface
{
    private bool $dryRun = false;
    private array $actions = [];

    public function __construct(
        private GeneratorInterface $entityGenerator,
        private GeneratorInterface $controllerGenerator,
        private GeneratorInterface $queryGenerator,
        private GeneratorInterface $repositoryGenerator,
        private GeneratorInterface $queryHandlerGenerator,
    )
    {
    }

    public function setDryRun(bool $dryRun): void
    {
        $this->dryRun = $dryRun;
    }

    public function generate(string $context, string $entityName, array $fields)
    {
        if ($this->dryRun) {
            $this->setUpDryRun();
        }

        $this->entityGenerator->generate($context, $entityName, $fields);
        $this->controllerGenerator->generate($context, $entityName, $fields);
        $this->queryGenerator->generate($context, $entityName, $fields);
        $this->repositoryGenerator->generate($context, $entityName, $fields);
        $this->queryHandlerGenerator->generate($context, $entityName, $fields);
    }

    public function getActions(): array
    {
        $this->actions = array_merge(
            $this->actions,
            $this->entityGenerator->getActions(),
            $this->controllerGenerator->getActions(),
            $this->queryGenerator->getActions(),
            $this->repositoryGenerator->getActions(),
            $this->queryHandlerGenerator->getActions(),
        );
        return $this->actions;
    }

    public function addAction(string $action): void
    {
        $this->actions[] = $action;
    }

    public function setNamespace(string $namespace): void
    {
        $this->entityGenerator->setNamespace($namespace);
        $this->controllerGenerator->setNamespace($namespace);
        $this->queryGenerator->setNamespace($namespace);
        $this->repositoryGenerator->setNamespace($namespace);
        $this->queryHandlerGenerator->setNamespace($namespace);
    }

    private function setUpDryRun()
    {
        $this->entityGenerator->setDryRun(true);
        $this->controllerGenerator->setDryRun(true);
        $this->queryGenerator->setDryRun(true);
        $this->repositoryGenerator->setDryRun(true);
        $this->queryHandlerGenerator->setDryRun(true);
    }
}
