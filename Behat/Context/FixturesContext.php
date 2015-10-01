<?php

namespace LaFourchette\FixturesBundle\Behat\Context;

use Behat\Behat\Context\Context as BaseContext;
use Behat\Gherkin\Node\TableNode;
use LaFourchette\FixturesBundle\Registry;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FixturesContext implements BaseContext
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Registry
     */
    protected $fixtureLoaderRegistry;

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
