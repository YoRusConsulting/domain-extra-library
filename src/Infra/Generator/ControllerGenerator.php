<?php

namespace YoRus\DomainExtraLibrary\Infra\Generator;

use YoRus\DomainExtraLibrary\Domain\Generator\GeneratorInterface;
use Symfony\Component\String\UnicodeString;
use function Symfony\Component\String\u;

class ControllerGenerator implements GeneratorInterface
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

    private function generateFile(string $context, string $controllerName, array $fields)
    {
        $contextPath = $context ? u($context)->camel()->title() . '/' : '';
        $path = $this->projectDir . '/src/' . $contextPath. 'UI/Controller/' .
            u($controllerName)->camel()->title() . 'Controller.php';

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

        $content = $this->generateContent($context, $controllerName, $fields);

        // Write content to file if dry run is disabled
        $this->addAction('Creating file: ' . $path);
        if (!$this->dryRun) {
            file_put_contents($path, $content);
        }
    }

    private function generateContent(string $context, string $entityName, array $fields): string
    {
        $entityRoute = u($entityName)->lower();
        $entityName = u($entityName)->camel()->title();
        $primary = $this->getPrimaryProperty($fields);
        $primaryName = u($primary['name']);

        // Generating header of the file with namespace and use statements
        $contextNmsp = $context ? u($context)->camel()->title() . '\\' : '';
        $content = '<?php' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= 'namespace ' . $this->namespace . $contextNmsp . 'UI\Controller;' . PHP_EOL;
        $content .= PHP_EOL;

        $content .= 'use ' . $this->namespace . $contextNmsp . 'App\Query\\' . $entityName . 'ListQuery;' . PHP_EOL;
        $content .= 'use ' . $this->namespace . $contextNmsp . 'App\Query\\' . $entityName . 'By'
            .  $primaryName->camel()->title(). 'Query;' . PHP_EOL;
        $content .= 'use OpenApi\Attributes as OA;' . PHP_EOL;
        $content .= 'use Symfony\Component\HttpFoundation\Response;' . PHP_EOL;
        $content .= 'use Symfony\Component\HttpFoundation\JsonResponse;' . PHP_EOL;
        $content .= 'use Symfony\Component\Messenger\MessageBusInterface;' . PHP_EOL;
        $content .= 'use Symfony\Component\Messenger\Stamp\HandledStamp;' . PHP_EOL;
        $content .= 'use Symfony\Component\Routing\Attribute\Route;' . PHP_EOL;
        $content .= 'use Symfony\Component\Serializer\Exception\ExceptionInterface;' . PHP_EOL;
        $content .= 'use Symfony\Component\Serializer\Normalizer\NormalizerInterface;' . PHP_EOL;
        $content .= PHP_EOL;

        // Generating class definition
        $content .= 'readonly class ' . $entityName . 'Controller' . PHP_EOL;
        $content .= '{' . PHP_EOL;

        // Generating constructor
        $content .= '    public function __construct(' . PHP_EOL;
        $content .= '        private MessageBusInterface $queryBus,' . PHP_EOL;
        $content .= '        private MessageBusInterface $commandBus,' . PHP_EOL;
        $content .= '        private NormalizerInterface $normalizer,' . PHP_EOL;
        $content .= '    )' . PHP_EOL;
        $content .= '    {' . PHP_EOL;
        $content .= '    }' . PHP_EOL;
        $content .= PHP_EOL;

        // Generating list endpoint
        $content .= '    /**' . PHP_EOL;
        $content .= '     * @throws ExceptionInterface' . PHP_EOL;
        $content .= '     */' . PHP_EOL;
        $content .= '    #[Route(\'/' . $entityRoute . 's\', name: \'' . $entityRoute . '_list\', methods: [\'GET\'], format: \'json\')]' . PHP_EOL;
        $content .= '    public function list(): Response' . PHP_EOL;
        $content .= '    {' . PHP_EOL;
        $content .= '        $query = ' . $entityName . 'ListQuery::fromRequest();' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= '        $envelope = $this->queryBus->dispatch($query);' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= '        /** @var HandledStamp $handledStamp */' . PHP_EOL;
        $content .= '        $handledStamp = $envelope->last(HandledStamp::class);' . PHP_EOL;
        $content .= '        $data = $handledStamp->getResult();' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= '        return new JsonResponse(' . PHP_EOL;
        $content .= '            $this->normalizer->normalize($data),' . PHP_EOL;
        $content .= '            Response::HTTP_OK,' . PHP_EOL;
        $content .= '        );' . PHP_EOL;
        $content .= '    }' . PHP_EOL;
        $content .= PHP_EOL;

        // Generating get by property key endpoint
        $content .= '    /**' . PHP_EOL;
        $content .= '     * @throws ExceptionInterface' . PHP_EOL;
        $content .= '     */' . PHP_EOL;
        $content .= '    #[Route(\'/' . $entityRoute . '/{' . $primaryName->camel() . '}\', name: \'' .
            $entityRoute . '_by_' . $primaryName->snake() . '\', methods: [\'GET\'], format: \'json\')]' . PHP_EOL;
        $content .= '    public function getBy' . $primaryName->camel()->title() .
            '(string $' . $primaryName->camel() . '): Response' . PHP_EOL;
        $content .= '    {' . PHP_EOL;
        $content .= '        $query = ' . $entityName . 'By' . $primaryName->camel()->title() . 'Query::with'
            . $primaryName->camel()->title()
            . '($' . $primaryName->camel() . ');' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= '        $envelope = $this->queryBus->dispatch($query);' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= '        /** @var HandledStamp $handledStamp */' . PHP_EOL;
        $content .= '        $handledStamp = $envelope->last(HandledStamp::class);' . PHP_EOL;
        $content .= '        $data = $handledStamp->getResult();' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= '        return new JsonResponse(' . PHP_EOL;
        $content .= '            $this->normalizer->normalize($data),' . PHP_EOL;
        $content .= '            Response::HTTP_OK,' . PHP_EOL;
        $content .= '        );' . PHP_EOL;
        $content .= '    }' . PHP_EOL;
        $content .= PHP_EOL;

        $content .= '}' . PHP_EOL;

        return $content;
    }
}
