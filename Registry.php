<?php

namespace LaFourchette\FixturesBundle;

use LaFourchette\FixturesBundle\Loader\FixturesLoader;

class Registry
{
    const LOADER_TAG = 'fixtures.loader';

    /**
     * @var array|FixturesLoader[]
     */
    private $loaders = [];

    /**
     * @param string $entityManagerName
     *
     * @throws \Exception
     *
     * @return FixturesLoader
     */
    public function getLoader($entityManagerName)
    {
        if (!isset($this->loaders[$entityManagerName])) {
            throw new \Exception(sprintf('loaders %s is not registered in loaders registry', $entityManagerName));
        }

        return $this->loaders[$entityManagerName];
    }

    /**
     * @param string         $entityManagerName
     * @param FixturesLoader $loader
     *
     * @throws \Exception
     */
    public function addLoader($entityManagerName, FixturesLoader $loader)
    {
        if (isset($this->loaders[$entityManagerName])) {
            throw new \Exception(sprintf('loaders %s is already set in registry', $entityManagerName));
        }

        $this->loaders[$entityManagerName] = $loader;
    }
}
