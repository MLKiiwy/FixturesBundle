<?php

namespace LaFourchette\FixturesBundle\Tests;

use LaFourchette\FixturesBundle\Registry;

class RegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage loaders plop is not registered in loaders registry
     */
    public function testExceptionGetLoader()
    {
        $registry = new Registry();
        $registry->getLoader('plop');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage loaders plop is already set in registry
     */
    public function testExceptionAddLoader()
    {
        $fixturesLoaderMock = $this->prophesize('LaFourchette\FixturesBundle\Loader\FixturesLoader');

        $registry = new Registry();
        $registry->addLoader('plop', $fixturesLoaderMock->reveal());
        $registry->addLoader('plop', $fixturesLoaderMock->reveal());
    }

    public function testGetLoader()
    {
        $fixturesLoaderMock = $this->prophesize('LaFourchette\FixturesBundle\Loader\FixturesLoader');

        $registry = new Registry();
        $registry->addLoader('plop', $fixturesLoaderMock->reveal());
        $this->assertEquals($registry->getLoader('plop'), $fixturesLoaderMock->reveal());
    }
}
