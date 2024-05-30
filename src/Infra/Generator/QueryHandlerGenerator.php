<?php

namespace YoRus\DomainExtraLibrary\Infra\Generator;

use YoRus\DomainExtraLibrary\Domain\Generator\GeneratorInterface;

use function Symfony\Component\String\u;

class QueryHandlerGenerator implements GeneratorInterface
{
    use PropertyTrait;

    private bool $dryRun = false;
    private array $actions = [];
    private string $namespace;

    public function __construct(private string $projectDir)
    {
    }

    public function generate(string $context, string $entityName, array $fields)
    {
        $this->generateQueryHandlerFiles($context, $entityName, $fields);
    }

    public function setDryRun(bool $dryRun): void
    {
        $this->dryRun = $dryRun;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function addAction(string $action): void
    {
        $this->actions[] = $action;
    }


    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    private function generateQueryHandlerFiles(string $context, string $controllerName, array $fields)
    {
        $primary = $this->getPrimaryProperty($fields);
        $primaryName = u($primary['name']);

        $byPrimaryQuery = 'By' . $primaryName->camel()->title() . 'QueryHandler';
        $queriesToGenerate = [
            'ListQueryHandler' => 'generateListQueryHandlerContent',
            $byPrimaryQuery => "generateByPrimaryQueryHandlerContent",
        ];
        $contextPath = $context ? u($context)->camel()->title() . '/' : '';
        foreach ($queriesToGenerate as $query => $method) {
            $path = $this->projectDir . '/src/' . $contextPath. 'App/QueryHandler/' .
                u($controllerName)->camel()->title() . $query . '.php';

            // Check if file already exists, do nothing if it does (do not overwrite)
            if (file_exists($path)) {
                $this->addAction('File already exists: ' . $path);
                return;
            }

            // Create directory if it does not exist
            // Do nothing if dry run is enabled
            if (!$this->dryRun && !is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            $content = $this->{$method}($context, $controllerName, $fields);

            // Write content to file if dry run is disabled
            $this->addAction('Creating file: ' . $path);
            if (!$this->dryRun) {
                file_put_contents($path, $content);
            }
        }
    }

    private function generateListQueryHandlerContent(string $context, string $entityName, array $fields): string
    {
        $entity = u($entityName);
        $entityName = u($entityName)->camel()->title();

        // Generating header of the file with namespace and use statements
        $contextNmsp = $context ? u($context)->camel()->title() . '\\' : '';
        $content = '<?php' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= 'namespace ' . $this->namespace . $contextNmsp . 'App\QueryHandler;' . PHP_EOL;
        $content .= PHP_EOL;

        $content .= 'use ' . $this->namespace . $contextNmsp . 'App\Query\\' . $entityName . 'ListQuery;' . PHP_EOL;
        $content .= 'use ' . $this->namespace . $contextNmsp . 'Domain\Entity\\' . $entityName . ';' . PHP_EOL;
        $content .= 'use ' . $this->namespace . $contextNmsp . 'Domain\Repository\\' . $entityName . 'ReaderInterface;' . PHP_EOL;

        // Generating class definition
        $content .= 'class ' . $entityName . 'ListQueryHandler' . PHP_EOL;
        $content .= '{' . PHP_EOL;

        // Generating constructor
        $content .= '    public function __construct(private readonly ' . $entityName . 'ReaderInterface $'
            . $entity->camel() . 'Reader)' . PHP_EOL;
        $content .= '    {' . PHP_EOL;
        $content .= '    }' . PHP_EOL;
        $content .= PHP_EOL;

        // Generating __invoke method
        $content .= '    public function __invoke(' . $entityName . 'ListQuery $'
            . $entity->camel() . 'ListQuery): array' . PHP_EOL;
        $content .= '    {' . PHP_EOL;
        $content .= '        return $this->' . $entity->camel() . 'Reader->findAll();' . PHP_EOL;
        $content .= '    }' . PHP_EOL;

        $content .= '}' . PHP_EOL;

        return $content;
    }

    private function generateByPrimaryQueryHandlerContent(string $context, string $entityName, array $fields): string
    {
        $entity = u($entityName);
        $entityName = u($entityName)->camel()->title();
        $primary = $this->getPrimaryProperty($fields);
        $primaryName = u($primary['name']);

        // Generating header of the file with namespace and use statements
        $contextNmsp = $context ? u($context)->camel()->title() . '\\' : '';
        $content = '<?php' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= 'namespace ' . $this->namespace . $contextNmsp . 'App\QueryHandler;' . PHP_EOL;
        $content .= PHP_EOL;

        $content .= 'use ' . $this->namespace . $contextNmsp . 'App\Query\\' . $entityName . 'By'
            . $primaryName->camel()->title() . 'Query;' . PHP_EOL;
        $content .= 'use ' . $this->namespace . $contextNmsp . 'Domain\Entity\\' . $entityName . ';' . PHP_EOL;
        $content .= 'use ' . $this->namespace . $contextNmsp . 'Domain\Repository\\' . $entityName . 'ReaderInterface;' . PHP_EOL;
        $content .= PHP_EOL;

        // Generating class definition
        $content .= 'class ' . $entityName . 'By' . $primaryName->camel()->title() . 'QueryHandler' . PHP_EOL;
        $content .= '{' . PHP_EOL;

        // Generating constructor
        $content .= '    public function __construct(private readonly ' . $entityName . 'ReaderInterface $'
            . $entity->camel() . 'Reader)' . PHP_EOL;
        $content .= '    {' . PHP_EOL;
        $content .= '    }' . PHP_EOL;
        $content .= PHP_EOL;

        // Generating __invoke method
        $content .= '    public function __invoke(' . $entityName . 'By'
            . $primaryName->camel()->title() . 'Query $'
            . $entity->camel() . 'By' . $primaryName->camel()->title() . 'Query): ?' . $entityName . PHP_EOL;
        $content .= '    {' . PHP_EOL;
        $content .= '        return $this->' . $entity->camel() . 'Reader->findBy' . $primaryName->camel()->title()
            . '($' . $entity->camel() . 'By' . $primaryName->camel()->title() . 'Query' . '->get'
            . $primaryName->camel()->title() . '());' . PHP_EOL;
        $content .= '    }' . PHP_EOL;

        $content .= '}' . PHP_EOL;

        return $content;
    }
}
