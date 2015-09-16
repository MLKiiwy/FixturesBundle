<?php

namespace LaFourchette\FixturesBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use LaFourchette\FixturesBundle\Registry;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FixturesContext implements Context
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Registry
     */
    private $fixtureLoaderRegistry;

    /**
     * @param ContainerInterface $container
     * @param Registry           $fixtureLoaderRegistry
     */
    public function __construct(ContainerInterface $container, Registry $fixtureLoaderRegistry)
    {
        $this->container = $container;
        $this->fixtureLoaderRegistry = $fixtureLoaderRegistry;
    }

    /**
     * @Given I have data in :connectionName database with:
     *
     * @param string    $connectionName
     * @param TableNode $tableNode
     */
    public function setDataInConnectionWithFixtureList($connectionName, TableNode $tableNode)
    {
        $fixtures = [];
        foreach ($tableNode->getRows() as $row) {
            $fixtures[] = $row[0];
        }
        $this->loadFixtures($connectionName, $fixtures);
    }

    /**
     * @param string   $connectionName
     * @param string[] $fixtures
     */
    public function loadFixtures($connectionName, $fixtures)
    {
        $this->fixtureLoaderRegistry->getLoader($connectionName)->load($fixtures);
    }

    /**
     * @Given I purge data in :connectionName database
     *
     * @param string $connectionName
     */
    public function purgeDatabase($connectionName)
    {
        $this->fixtureLoaderRegistry->getLoader($connectionName)->purgeDatabase();
    }
}
