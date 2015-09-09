<?php

namespace LaFourchette\FixturesBundle\Tests;

use LaFourchette\FixturesBundle\FixturesBundle;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FixturesBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        /** @var ContainerBuilder|ObjectProphecy $containerBuilderMock */
        $containerBuilderMock = $this->prophesize('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilderMock
            ->addCompilerPass(Argument::type('FixturesBundle\DependencyInjection\Compiler\RegistryCompilerPass'))
            ->shouldBeCalledTimes(1);

        $fixturesBundle = new FixturesBundle();
        $fixturesBundle->build($containerBuilderMock->reveal());
    }
}
