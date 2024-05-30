<?php
namespace YoRus\DomainExtraLibrary\UI\ConsoleCommand\DDD\CQRS;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use YoRus\DomainExtraLibrary\Domain\Bundle\Configuration;
use YoRus\DomainExtraLibrary\Domain\Generator\GeneratorInterface;

use function Symfony\Component\String\u;

class GenerateConsoleCommand extends Command
{
    public function __construct(
        private GeneratorInterface $generator,
        private Configuration $configuration,
    )
    {
        parent::__construct();

        $this->generator->setNamespace($this->configuration->getNamespace());
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this->setName('yorus:ddd-cqrs:generate')
            ->setDescription('Generate all the DDD CQRS classes for a given entity with some properties');

        $help =<<<HELP
Generate all the DDD CQRS classes for a given entity with some properties.
The properties list will be asked to the user.
If the property is empty, the command will stop asking for more properties.

To provide a primary key, use the following format: property* (with the star at the end of the property name).
To provide the type of the property, use the following format: property:type (with the colon and the type at the end of the property name).
To provide a nullable property, use the following format: property:?type (with the question mark before the type of the property).

For example:
- id*:int
- name:string
- description:?string

If not type is provided, it will be considered as a string.

HELP;

            $this->setHelp($help)
                ->addOption('dry-run', 'dr', InputOption::VALUE_NONE, 'Perform a dry-run and show the changes that will be made. Nothing will be executed.')
                ->addOption('context', 'c', InputOption::VALUE_REQUIRED, 'Generate the classes in the specified context', '');
    }

    /**
     * @inheritdoc
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Generating DDD CQRS classes');

        // Retrieve the entity name
        $entity = $io->ask('Please enter the name of the entity', null, function ($entity) {
            if (empty($entity)) {
                throw new \RuntimeException('Entity cannot be empty.');
            }
            return $entity;
        });

        // Retrieve the properties
        $properties = [];
        $index = 1;
        $hasPrimaryProperty = false;
        while (true) {
            $property = $io->ask('Please enter the name of the property #' . $index++
                . "\n ENTER if no more property to add", null, function ($property) {

                if ($property === null) {
                    return null;
                }

                // Validate the property format
                $regex = '/^[a-zA-Z_][a-zA-Z0-9_]*(\*?)?(:\??[a-zA-Z_][a-zA-Z0-9_]*)?$/';
                if (!preg_match($regex, $property)) {
                    throw new \RuntimeException('Invalid property format.');
                }
                return $property;
            });

            // If the property is empty, we stop asking for more properties
            if (empty($property)) {
                break;
            }

            // Add the property to the list
            $propertyParts = explode(':', $property);
            if (count($propertyParts) === 1) {
                $propertyParts[] = 'string';
            }

            // Extract the property information
            $propertyInfo = [
                'name' => $propertyParts[0],
                'type' => $propertyParts[1],
                'primary' => substr($propertyParts[0], -1) === '*',
                'nullable' => $propertyParts[1][0] === '?',
            ];

            // Remove the special characters from the property name (primary key)
            if ($propertyInfo['primary']) {
                $propertyInfo['name'] = substr($propertyInfo['name'], 0, -1);
                if ($hasPrimaryProperty) {
                    throw new \RuntimeException('Only one primary key is allowed.');
                }
                $hasPrimaryProperty = true;
            }

            // Remove the special characters from the property type (nullable)
            if ($propertyInfo['nullable']) {
                $propertyInfo['type'] = substr($propertyInfo['type'], 1);
            }
            $properties[] = $propertyInfo;
        }

        if (empty($properties)) {
            throw new \RuntimeException('No property provided.');
        }
        if ($hasPrimaryProperty === false) {
            throw new \RuntimeException('No primary key provided.');
        }

        $dryRun = $input->getOption('dry-run');
        $this->generateClasses(
            $entity,
            $properties,
            $dryRun,
            $input->getOption('context'),
        );

        if ($dryRun) {
            $io->warning('Dry-run enabled. No file will be created.');
        }

        $io->writeln(sprintf(
            'Asking for generation of `%s` domain with %d properties in context `%s`. Actions performed:',
            u($entity)->camel()->title(),
            count($properties),
            u($input->getOption('context'))->camel()->title(),
        ));
        $io->listing($this->generator->getActions());
        $io->success('Classes generated successfully: ');

        return Command::SUCCESS;
    }

    private function generateClasses(string $entity, array $properties, bool $dryRun, string $context): void
    {
        $this->generator->setDryRun($dryRun);
        $this->generator->generate($context, $entity, $properties);
    }
}
