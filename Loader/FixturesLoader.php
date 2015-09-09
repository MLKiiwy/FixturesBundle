<?php

namespace LaFourchette\FixturesBundle\Loader;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Nelmio\Alice\Fixtures;
use Nelmio\Alice\ProcessorInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class FixturesLoader
{
    const RESOURCE_FILE_SUFFIX = '.yml';
    const SET_PLACEHOLDER = '{set}';
    const GROUP_JOKER = '@Group';

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ProcessorInterface
     */
    private $fixturesDataProcessor;

    /**
     * @var array
     */
    private $providers = [];

    /**
     * @var array
     */
    private $groups;

    /**
     * @var array
     */
    private $dependencies;

    /**
     * @param EntityManager   $entityManager
     * @param KernelInterface $kernel
     */
    public function __construct(EntityManager $entityManager, KernelInterface $kernel)
    {
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
    }

    /**
     * @param array $fixtures
     * @param bool  $purge
     */
    public function load(array $fixtures, $purge = true)
    {
        if ($purge) {
            $this->purgeDatabase();
        }

        $loader = new Fixtures($this->entityManager);
        $loader->addProcessor($this->fixturesDataProcessor);

        $options = ['providers' => $this->providers];

        $fixtures = $this->generateFixtureList($fixtures);

        foreach ($fixtures as $fixture) {
            $loader->loadFiles($this->getFixturePathByName($fixture), $options);
        }
    }

    public function purgeDatabase()
    {
        $connection = $this->entityManager->getConnection();

        $connection->exec('SET FOREIGN_KEY_CHECKS = 0;');

        $purger = new ORMPurger($this->entityManager);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $purger->purge();

        $connection->exec('SET FOREIGN_KEY_CHECKS = 1;');
    }

    /**
     * @param string $fixtureName
     *
     * @return string
     */
    private function getFixturePathByName($fixtureName)
    {
        return $this->kernel->locateResource($fixtureName.self::RESOURCE_FILE_SUFFIX);
    }

    /**
     * @param array $groups
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
    }

    /**
     * @param array $dependencies
     */
    public function setDependencies($dependencies)
    {
        $this->dependencies = $dependencies;
    }

    /**
     * @param array $providerClasses
     */
    public function setProviderClasses($providerClasses)
    {
        $providers = [];
        foreach ($providerClasses as $class) {
            $providers[] = new $class();
        }
        $this->providers = $providers;
    }

    /**
     * @param ProcessorInterface $fixturesDataProcessor
     */
    public function setFixturesDataProcessor(ProcessorInterface $fixturesDataProcessor)
    {
        $this->fixturesDataProcessor = $fixturesDataProcessor;
    }

    /**
     * @param mixed $element
     *
     * @return bool
     */
    private function isGroupElement($element)
    {
        return is_string($element) && substr($element, 0, strlen(self::GROUP_JOKER)) === self::GROUP_JOKER;
    }

    /**
     * @param $element
     * @param array       $fixtureList
     * @param null|string $set
     *
     * @throws \Exception
     *
     * @return array
     */
    private function generateFixtureList($element, array $fixtureList = [], $set = null)
    {
        if (is_array($element)) {
            foreach ($element as $subElement) {
                $fixtureList = $this->generateFixtureList($subElement, $fixtureList, $set);
            }
        } else {
            if ($this->isGroupElement($element)) {
                $element = $this->replaceSet($element, $set);
                list(, $group, $groupSet) = explode(':', $element);

                if (!isset($this->groups[$group])) {
                    throw new \Exception(sprintf('fixture group %s is not defined', $group));
                }

                $fixtureList = $this->generateFixtureList($this->groups[$group], $fixtureList, $groupSet);
            } else {
                list($fixturePath, $fixtureSet) = strpos($element, ':') !== false ? explode(':', $element) : [$element, null];
                $fixtureSet = empty($fixtureSet) ? $set : $fixtureSet;
                $realFixturePath = empty($fixtureSet) ? $fixturePath : $this->replaceSet($fixturePath, $fixtureSet);

                if (!in_array($realFixturePath, $fixtureList)) {
                    if (isset($this->dependencies[$fixturePath])) {
                        $fixtureList = $this->generateFixtureList($this->dependencies[$fixturePath], $fixtureList, $fixtureSet);
                    }

                    $fixtureList[] = $realFixturePath;
                }
            }
        }

        return $fixtureList;
    }

    /**
     * @param string $fixturePath
     * @param string $set
     *
     * @return string
     */
    private function replaceSet($fixturePath, $set)
    {
        return str_replace(self::SET_PLACEHOLDER, $set, $fixturePath);
    }
}
