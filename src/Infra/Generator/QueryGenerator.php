<?php

namespace YoRus\DomainExtraLibrary\Infra\Generator;

use YoRus\DomainExtraLibrary\Domain\Generator\GeneratorInterface;
//use Symfony\Component\String\UnicodeString;
use function Symfony\Component\String\u;

class QueryGenerator implements GeneratorInterface
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
        $this->generateQueryFiles($context, $entityName, $fields);
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

    private function generateQueryFiles(string $context, string $controllerName, array $fields)
    {
        $primary = $this->getPrimaryProperty($fields);
        $primaryName = u($primary['name']);

        $byPrimaryQuery = 'By' . $primaryName->camel()->title() . 'Query';
        $queriesToGenerate = [
            'ListQuery' => 'generateListQueryContent',
            $byPrimaryQuery => "generateByPrimaryQueryContent",
        ];
        $contextPath = $context ? u($context)->camel()->title() . '/' : '';
        foreach ($queriesToGenerate as $query => $method) {
            $path = $this->projectDir . '/src/' . $contextPath. 'App/Query/' .
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

    private function generateListQueryContent(string $context, string $entityName, array $fields): string
    {
        $entityRoute = u($entityName)->lower();
        $entityName = u($entityName)->camel()->title();

        // Generating header of the file with namespace and use statements
        $contextNmsp = $context ? u($context)->camel()->title() . '\\' : '';
        $content = '<?php' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= 'namespace ' . $this->namespace . $contextNmsp . 'App\Query;' . PHP_EOL;
        $content .= PHP_EOL;

        // Generating class definition
        $content .= 'class ' . $entityName . 'ListQuery' . PHP_EOL;
        $content .= '{' . PHP_EOL;

        // Generating constructor
        $content .= '    public function __construct()' . PHP_EOL;
        $content .= '    {' . PHP_EOL;
        $content .= '    }' . PHP_EOL;
        $content .= PHP_EOL;

        // Generating fromRequest method
        $content .= '    public static function fromRequest(): self' . PHP_EOL;
        $content .= '    {' . PHP_EOL;
        $content .= '        return new self();' . PHP_EOL;
        $content .= '    }' . PHP_EOL;

        $content .= '}' . PHP_EOL;

        return $content;
    }

    private function generateByPrimaryQueryContent(string $context, string $entityName, array $fields): string
    {
        $entityRoute = u($entityName)->lower();
        $entityName = u($entityName)->camel()->title();
        $primary = $this->getPrimaryProperty($fields);
        $primaryName = u($primary['name']);

        // Generating header of the file with namespace and use statements
        $contextNmsp = $context ? u($context)->camel()->title() . '\\' : '';
        $content = '<?php' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= 'namespace ' . $this->namespace . $contextNmsp . 'App\Query;' . PHP_EOL;
        $content .= PHP_EOL;

        $primaryTypeMatch = $this->getPrimaryPropertyTypeMatch($fields);
        if ($primaryTypeMatch === 'UuidInterface') {
            $content .= 'use Ramsey\Uuid\Uuid;' . PHP_EOL;
            $content .= 'use Ramsey\Uuid\UuidInterface;' . PHP_EOL;
            $content .= PHP_EOL;
        }

        // Generating class definition
        $content .= 'class ' . $entityName . 'By' . $primaryName->camel()->title() . 'Query' . PHP_EOL;
        $content .= '{' . PHP_EOL;

        // Generating constructor
        $content .= '    public function __construct(private readonly string $'
            .  $primaryName->camel() . ')' . PHP_EOL;
        $content .= '    {' . PHP_EOL;
        $content .= '    }' . PHP_EOL;
        $content .= PHP_EOL;

        // Generating withId method
        $content .= '    public static function with' . $primaryName->camel()->title()
            . '(string $' . $primaryName->camel() . '): self' . PHP_EOL;
        $content .= '    {' . PHP_EOL;
        $content .= '        return new self($' . $primaryName->camel() . ');' . PHP_EOL;
        $content .= '    }' . PHP_EOL;

        // Generating getId method

        $content .= '    public function get' . $primaryName->camel()->title() . '(): '
            . $primaryTypeMatch . PHP_EOL;
        $content .= '    {' . PHP_EOL;
        if ($primaryTypeMatch === 'UuidInterface') {
            $content .= '        return Uuid::fromString($this->' .  $primaryName->camel() . ');' . PHP_EOL;
        } else {
            $content .= '        return $this->' .  $primaryName->camel() . ';' . PHP_EOL;
        }
        $content .= '    }' . PHP_EOL;

        $content .= '}' . PHP_EOL;

        return $content;
    }
}
