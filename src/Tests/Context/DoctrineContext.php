<?php

namespace AppInWeb\DomainExtraLibrary\Tests\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Doctrine\ORM\EntityManagerInterface;

/**
 * DoctrineContext.
 *
 * @uses \Context
 */
class DoctrineContext implements Context
{
    use KernelDictionary;

    /** @var string $truncateSql */
    private static $truncateSql;

    /** @var string[] $entityMapping */
    private $entityMapping;

    /**
     * @var string
     */
    protected $lastSqlRequest;

    /**
     * @var mixed[]
     */
    protected $dataSet;

    /**
     * @param string[] $mapping
     */
    public function __construct(array $mapping)
    {
        $this->entityMapping = $mapping;
    }

    /**
     * @BeforeScenario @database
     */
    public function beforeScenario(): void
    {
        if (null === static::$truncateSql) {
            static::$truncateSql = $this->generateTruncateSql();
        }

        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->getConnection()->executeUpdate(static::$truncateSql);
    }


    /**
     * @param string $entity
     * @param string $identifier
     *
     * @throws \Exception
     *
     * @Given I should have a :entity entity with identifier :identifier
     */
    public function iShouldHaveAEntityWithIdentifier(string $entity, string $identifier): void
    {
        $entity = $this->findEntityWithIdentifier($entity, $identifier);

        if (null === $entity) {
            throw new \Exception('Entity not found');
        }
    }


    /**
     * @param string $entity
     * @param string $identifier
     *
     * @throws \Exception
     *
     * @Then I should not have a :entity entity with identifier :identifier
     */
    public function iShouldNotHaveAEntityWithIdentifier(string $entity, string $identifier): void
    {
        $entity = $this->findEntityWithIdentifier($entity, $identifier);

        if (null !== $entity) {
            throw new \Exception('Entity found');
        }
    }

    /**
     * @param string $query
     *
     * @Then I execute SQL query:
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function executeSqlQuery(string $query): void
    {
        $this->executeSql($query);
    }

    /**
     * @param TableNode $expectedData
     *
     * @throws \Exception
     *
     * @Then the result set must be:
     */
    public function theResultSetMustBe(TableNode $expectedData): void
    {
        if (count($expectedData->getHash()) < count($this->dataSet)) {
            throw new \Exception(sprintf('The expected number of rows is lower than the number of rows obtained [expected: "%s", dataSet: "%s"]', count($expectedData->getHash()), count($this->dataSet)));
        }

        if (count($expectedData->getHash()) > count($this->dataSet)) {
            throw new \Exception(sprintf('The expected number of rows is greater than the number of rows obtained [expected: "%s", dataSet: "%s"]', count($expectedData->getHash()), count($this->dataSet)));
        }

        foreach ($expectedData->getHash() as $rowNum => $expected) {
            foreach ($expected as $expectedColumnName => $expectedColumnValue) {
                $columnValue = $this->dataSet[$rowNum][strtolower($expectedColumnName)];

                if ($expectedColumnValue !== $columnValue) {
                    throw new \Exception(sprintf('Invalid result for query "%s", expected "%s" and found "%s"', $this->lastSqlRequest, $expectedColumnValue, $columnValue));
                }
            }
        }
    }

    /**
     * @param string $table
     * @param string $expected
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @Then the rows number of :table table should be equals to :expected
     */
    public function theRowsNumberOfShouldBeEqualsTo(string $table, string $expected): void
    {
        $this->executeSql(sprintf('SELECT COUNT(*) FROM %s WHERE TRUE', $table));
        if ($expected !== $this->dataSet[0]['count']) {
            throw new \Exception(sprintf('Invalid rows number in table "%s", expected "%s" and found "%s"', $table, $expected, $this->dataSet[0]['count']));
        }
    }

    /**
     * @return string
     */
    private function generateTruncateSql(): string
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $tables = array_column(
            $entityManager->getConnection()->fetchAll("SELECT table_name FROM information_schema.tables WHERE table_schema='public' and table_name!='migration_versions'"),
            'table_name'
        );
        $platform = $entityManager->getConnection()->getDatabasePlatform();

        $sqlDisable = $sqlTruncates = $sqlEnable = [];

        foreach ($tables as $tbl) {
            $sqlDisable[] = 'ALTER TABLE "'.$tbl.'" DISABLE TRIGGER ALL;';
            $sqlTruncates[] = '"'.$tbl.'"';
            $sqlEnable [] = 'ALTER TABLE "'.$tbl.'" ENABLE TRIGGER ALL;';
        }

        $sqlTruncates = sprintf('truncate table %s;', implode(',', $sqlTruncates));

        return implode(chr(10), $sqlDisable).chr(10).
            $sqlTruncates.chr(10).
            implode(chr(10), $sqlEnable);
    }

    /**
     * @param string $entity
     * @param string $identifier
     *
     * @return object|null
     *
     * @throws \Exception
     */
    private function findEntityWithIdentifier(string $entity, string $identifier): ?object
    {
        $this->getEntityManager()->clear();

        if (false === array_key_exists($entity, $this->entityMapping)) {
            throw new \Exception(sprintf('Mapping for entity “%s“ is not defined.', $entity));
        }

        return $this->getEntityManager()->getRepository($this->entityMapping[$entity])->find($identifier);
    }

    /**
     * @return EntityManagerInterface
     */
    private function getEntityManager(): EntityManagerInterface
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * @param string $sql
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function executeSql(string $sql): void
    {
        $this->lastSqlRequest = $sql; // Log for debug
        $this->dataSet = $this->getEntityManager()->getConnection()->query($sql)->fetchAll();
    }
}
