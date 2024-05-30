<?php

namespace YoRus\DomainExtraLibrary\Infra\Generator;

use YoRus\DomainExtraLibrary\Domain\Generator\GeneratorInterface;
//use Symfony\Component\String\UnicodeString;
use function Symfony\Component\String\u;

class RepositoryGenerator implements GeneratorInterface
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
        $this->generateRepositoryFiles($context, $entityName, $fields);
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

    private function generateRepositoryFiles(string $context, string $controllerName, array $fields)
    {
        $queriesToGenerate = [
            'ReaderInterface' => [
                'path' => 'Domain/Repository',
                'method' => 'generateReaderInterfaceContent'
            ],
            'WriterInterface' => [
                'path' => 'Domain/Repository',
                'method' => 'generateWriterInterfaceContent'
            ],
            'Reader' => [
                'path' => 'Infra/Repository',
                'method' => 'generateReaderContent'
            ],
            'Writer' => [
                'path' => 'Infra/Repository',
                'method' => 'generateWriterContent'
            ],
        ];
        $contextPath = $context ? u($context)->camel()->title() . '/' : '';
        foreach ($queriesToGenerate as $repo => $info) {
            $path = $this->projectDir . '/src/' . $contextPath . $info['path'] . '/' .
                u($controllerName)->camel()->title() . $repo . '.php';

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

            $content = $this->{$info['method']}($context, $controllerName, $fields);

            // Write content to file if dry run is disabled
            $this->addAction('Creating file: ' . $path);
            if (!$this->dryRun) {
                file_put_contents($path, $content);
            }
        }
    }

    private function generateReaderInterfaceContent(string $context, string $entityName, array $fields): string
    {
        $entityRoute = u($entityName)->lower();
        $entityName = u($entityName)->camel()->title();
        $primary = $this->getPrimaryProperty($fields);
        $primaryName = u($primary['name']);
        $primaryTypeMatch = $this->getPrimaryPropertyTypeMatch($fields);

        // Generating header of the file with namespace and use statements
        $contextNmsp = $context ? u($context)->camel()->title() . '\\' : '';
        $content = '<?php' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= 'namespace ' . $this->namespace . $contextNmsp . 'Domain\Repository;' . PHP_EOL;
        $content .= PHP_EOL;

        $content .= 'use ' . $this->namespace . $contextNmsp . 'Domain\Entity\\' . $entityName . ';' . PHP_EOL;
        if ($primaryTypeMatch === 'UuidInterface') {
            $content .= 'use Ramsey\Uuid\UuidInterface;' . PHP_EOL;
            $content .= PHP_EOL;
        }

        // Generating class definition
        $content .= 'interface ' . $entityName . 'ReaderInterface' . PHP_EOL;
        $content .= '{' . PHP_EOL;

        // Generating methods
        $content .= '    /**' . PHP_EOL;
        $content .= '     * Get all ' . $entityRoute . 's' . PHP_EOL;
        $content .= '     *' . PHP_EOL;
        $content .= '     * @return ' . $entityName . '[]' . PHP_EOL;
        $content .= '     */' . PHP_EOL;
        $content .= '    public function findAll(): array;' . PHP_EOL;
        $content .= PHP_EOL;

        $content .= '    /**' . PHP_EOL;
        $content .= '     * @param ' . $primaryTypeMatch . ' $' . $primaryName->camel() . PHP_EOL;
        $content .= '     *' . PHP_EOL;
        $content .= '     * @return ' . $entityName . PHP_EOL;
        $content .= '     */' . PHP_EOL;
        $content .= '    public function findBy' . $primaryName->camel()->title() . '(' . $primaryTypeMatch . ' $' . $primaryName->camel() . '): '
            . $entityName . ';' . PHP_EOL;

        $content .= '}' . PHP_EOL;

        return $content;
    }

    private function generateWriterInterfaceContent(string $context, string $entityName, array $fields): string
    {
        $entityProperty = u($entityName)->camel();
        $entityName = $entityProperty->title();

        // Generating header of the file with namespace and use statements
        $contextNmsp = $context ? u($context)->camel()->title() . '\\' : '';
        $content = '<?php' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= 'namespace ' . $this->namespace . $contextNmsp . 'Domain\Repository;' . PHP_EOL;
        $content .= PHP_EOL;

        $content .= 'use ' . $this->namespace . $contextNmsp . 'Domain\Entity\\' . $entityName . ';' . PHP_EOL;

        // Generating class definition
        $content .= 'interface ' . $entityName . 'WriterInterface' . PHP_EOL;
        $content .= '{' . PHP_EOL;

        // Generating methods
        $content .= '    /**' . PHP_EOL;
        $content .= '     * @param ' . $entityName . ' $' . $entityProperty . PHP_EOL;
        $content .= '     */' . PHP_EOL;
        $content .= '    public function save(' . $entityName . ' $' . $entityProperty . '): void;' . PHP_EOL;
        $content .= PHP_EOL;

        $content .= '    /**' . PHP_EOL;
        $content .= '     * @param ' . $entityName . ' $' . $entityProperty . PHP_EOL;
        $content .= '     */' . PHP_EOL;
        $content .= '    public function delete(' . $entityName . ' $' . $entityProperty . '): void;' . PHP_EOL;

        $content .= '}' . PHP_EOL;

        return $content;
    }

    private function generateReaderContent(string $context, string $entityName, array $fields): string
    {
        $entityRoute = u($entityName)->lower();
        $entityName = u($entityName)->camel()->title();
        $primary = $this->getPrimaryProperty($fields);
        $primaryName = u($primary['name']);
        $primaryTypeMatch = $this->getPrimaryPropertyTypeMatch($fields);

        // Generating header of the file with namespace and use statements
        $contextNmsp = $context ? u($context)->camel()->title() . '\\' : '';
        $content = '<?php' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= 'namespace ' . $this->namespace . $contextNmsp . 'Infra\Repository;' . PHP_EOL;
        $content .= PHP_EOL;

        $content .= 'use ' . $this->namespace . $contextNmsp . 'Domain\Entity\\' . $entityName . ';' . PHP_EOL;
        $content .= 'use ' . $this->namespace . $contextNmsp . 'Domain\Repository\\' . $entityName . 'ReaderInterface;' . PHP_EOL;
        $content .= 'use Doctrine\ORM\EntityManagerInterface;' . PHP_EOL;
        if ($primaryTypeMatch === 'UuidInterface') {
            $content .= 'use Ramsey\Uuid\UuidInterface;' . PHP_EOL;
            $content .= PHP_EOL;
        }

        // Generating class definition
        $content .= 'class ' . $entityName . 'Reader' . ' implements ' . $entityName . 'ReaderInterface' . PHP_EOL;
        $content .= '{' . PHP_EOL;

        // Generating constructor
        $content .= '    /**' . PHP_EOL;
        $content .= '     * @param EntityManagerInterface $entityManager' . PHP_EOL;
        $content .= '     */' . PHP_EOL;
        $content .= '    public function __construct(' . PHP_EOL;
        $content .= '        private readonly EntityManagerInterface $entityManager' . PHP_EOL;
        $content .= '    ) {' . PHP_EOL;
        $content .= '    }' . PHP_EOL;
        $content .= PHP_EOL;


        // Generating methods
        $content .= '    /**' . PHP_EOL;
        $content .= '     * @inheritDoc' . PHP_EOL;
        $content .= '     */' . PHP_EOL;
        $content .= '    public function findAll(): array' . PHP_EOL;
        $content .= '    {' . PHP_EOL;
        $content .= '        $objectRepository = $this->entityManager->getRepository(' . $entityName . '::class);' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= '        return $objectRepository->findAll();' . PHP_EOL;
        $content .= '    }' . PHP_EOL;
        $content .= PHP_EOL;

        $content .= '    /**' . PHP_EOL;
        $content .= '     * @inheritDoc' . PHP_EOL;
        $content .= '     */' . PHP_EOL;
        $content .= '    public function findBy' . $primaryName->camel()->title()
            . '(' . $primaryTypeMatch . ' $' . $primaryName->camel() . '): '
            . $entityName . PHP_EOL;
        $content .= '    {' . PHP_EOL;
        $content .= '        $objectRepository = $this->entityManager->getRepository(' . $entityName . '::class);' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= '        return $objectRepository->findOneBy([\'' . $primaryName->camel() . '\' => $'
            . $primaryName->camel() . ']);' . PHP_EOL;
        $content .= '    }' . PHP_EOL;

        $content .= '}' . PHP_EOL;

        return $content;
    }

    private function generateWriterContent(string $context, string $entityName, array $fields): string
    {
        $entityProperty = u($entityName)->camel();
        $entityName = $entityProperty->title();
        $primary = $this->getPrimaryProperty($fields);
        $primaryName = u($primary['name']);
        $primaryTypeMatch = $this->getPrimaryPropertyTypeMatch($fields);

        // Generating header of the file with namespace and use statements
        $contextNmsp = $context ? u($context)->camel()->title() . '\\' : '';
        $content = '<?php' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= 'namespace ' . $this->namespace . $contextNmsp . 'Infra\Repository;' . PHP_EOL;
        $content .= PHP_EOL;

        $content .= 'use ' . $this->namespace . $contextNmsp . 'Domain\Entity\\' . $entityName . ';' . PHP_EOL;
        $content .= 'use ' . $this->namespace . $contextNmsp . 'Domain\Repository\\' . $entityName . 'WriterInterface;' . PHP_EOL;
        $content .= 'use Doctrine\ORM\EntityManagerInterface;' . PHP_EOL;

        // Generating class definition
        $content .= 'class ' . $entityName . 'Writer' . ' implements ' . $entityName . 'WriterInterface' . PHP_EOL;
        $content .= '{' . PHP_EOL;

        // Generating constructor
        $content .= '    /**' . PHP_EOL;
        $content .= '     * @param EntityManagerInterface $entityManager' . PHP_EOL;
        $content .= '     */' . PHP_EOL;
        $content .= '    public function __construct(' . PHP_EOL;
        $content .= '        private readonly EntityManagerInterface $entityManager' . PHP_EOL;
        $content .= '    ) {' . PHP_EOL;
        $content .= '    }' . PHP_EOL;
        $content .= PHP_EOL;

        // Generating methods
        $content .= '    /**' . PHP_EOL;
        $content .= '     * @inheritDoc' . PHP_EOL;
        $content .= '     */' . PHP_EOL;
        $content .= '    public function save(' . $entityName . ' $' . $entityProperty . '): void' . PHP_EOL;
        $content .= '    {' . PHP_EOL;
        $content .= '        $this->entityManager->persist($' . $entityProperty . ');' . PHP_EOL;
        $content .= '        $this->entityManager->flush();' . PHP_EOL;
        $content .= '    }' . PHP_EOL;
        $content .= PHP_EOL;

        $content .= '    /**' . PHP_EOL;
        $content .= '     * @inheritDoc' . PHP_EOL;
        $content .= '     */' . PHP_EOL;
        $content .= '    public function delete(' . $entityName . ' $' . $entityProperty . '): void' . PHP_EOL;
        $content .= '    {' . PHP_EOL;
        $content .= '        $this->entityManager->remove($' . $entityProperty . ');' . PHP_EOL;
        $content .= '        $this->entityManager->flush();' . PHP_EOL;
        $content .= '    }' . PHP_EOL;

        $content .= '}' . PHP_EOL;

        return $content;
    }
}
