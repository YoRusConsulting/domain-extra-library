<?php

namespace YoRus\DomainExtraLibrary\Infra\Generator;

use YoRus\DomainExtraLibrary\Domain\Generator\GeneratorInterface;
use Symfony\Component\String\UnicodeString;
use function Symfony\Component\String\u;

class EntityGenerator implements GeneratorInterface
{
    private bool $dryRun = false;
    private array $actions = [];
    private string $namespace;

    public function __construct(private string $projectDir)
    {
    }

    public function generate(string $context, string $entityName, array $fields)
    {
        $this->generateFile($context, $entityName, $fields);
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

    private function generateFile(string $context, string $entityName, array $fields)
    {
        $contextPath = $context ? u($context)->camel()->title() . '/' : '';
        $path = $this->projectDir . '/src/' . $contextPath . 'Domain/Entity/' . u($entityName)->camel()->title() . '.php';

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

        $content = $this->generateContent($context, $entityName, $fields);

        // Write content to file if dry run is disabled
        $this->addAction('Creating file: ' . $path);
        if (!$this->dryRun) {
            file_put_contents($path, $content);
        }
    }

    private function generateContent(string $context, string $entityName, array $fields): string
    {
        $entityName = u($entityName)->camel()->title();

        // Generating header of the file with namespace and use statements
        $contextNmsp = $context ? u($context)->camel()->title() . '\\' : '';
        $content = '<?php' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= 'namespace ' . $this->namespace . $contextNmsp . 'Domain\Entity;' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= 'use Ramsey\Uuid\UuidInterface;' . PHP_EOL;
        $content .= 'use Doctrine\ORM\Mapping as ORM;' . PHP_EOL;
        $content .= PHP_EOL;
        
        // Generating class definition with ORM annotations
        $content .= '#[ORM\Table(name: \'' . u($entityName)->snake() . '\')]' . PHP_EOL;
        $content .= '#[ORM\Entity()]' . PHP_EOL;
        $content .= 'class ' . $entityName . PHP_EOL;
        $content .= '{' . PHP_EOL;

        // Generating properties with ORM annotations
        $constructParams = '';
        $constructAffectation = '';
        $getters = '';
        foreach ($fields as $field) {
            $nullable = '';
            $nullableORM = '';
            if ($field['nullable']) {
                $nullable = '?';
                $nullableORM = ', nullable: true';
            }

            $fieldName = u($field['name'])->camel();
            $fieldType = $field['type'];
            if (in_array($fieldType, ['string', 'int', 'float', 'bool', 'uuid'])) {
                $content .= '    #[ORM\Column(name: \'' . u($field['name'])->snake()
                    . '\', type: \'' . $fieldType . '\''
                    . $nullableORM . ')]'
                    . PHP_EOL;
            }
            if ($field['primary']) {
                $content .= '    #[ORM\Id]' . PHP_EOL;
            }
            if ($fieldType === 'uuid') {
                $fieldType = 'UuidInterface';
            }
            $content .= '    private ' . $nullable . $fieldType . ' $' . $fieldName . ';' . PHP_EOL;
            $constructParams .= $nullable . $fieldType . ' $' . $fieldName . ', ';
            $constructAffectation .= '        $this->' . $fieldName . ' = $' . $fieldName . ';' . PHP_EOL;

            // Generating getters
            $getters .= '    public function get' . $fieldName->title() . '(): ' . $nullable . $fieldType . PHP_EOL;
            $getters .= '    {' . PHP_EOL;
            $getters .= '        return $this->' . $fieldName . ';' . PHP_EOL;
            $getters .= '    }' . PHP_EOL;
            $getters .= PHP_EOL;

            $content .= PHP_EOL;
        }
        $content .= PHP_EOL;
        
        // Generating constructor
        $constructParams = substr($constructParams, 0, -2);
        $content .= '    public function __construct(' . $constructParams . ')' . PHP_EOL;
        $content .= '    {' . PHP_EOL;
        $content .= $constructAffectation;
        $content .= '    }' . PHP_EOL;
        $content .= PHP_EOL;

        // Putting getters
        $content .= $getters;

        // Removing the last line break
        $content = substr($content, 0, -1);

        $content .= '}' . PHP_EOL;

        return $content;
    }
}