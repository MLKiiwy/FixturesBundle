<?php

namespace LaFourchette\FixturesBundle\Tests\DependencyInjection;

use LaFourchette\FixturesBundle\DependencyInjection\FixturesExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

class LaFourchetteCoreExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions()
    {
        return [new FixturesExtension()];
    }

    /**
     * @dataProvider dataProviderServices
     */
    public function testServices($service, $expected)
    {
        $this->load();
        $this->assertContainerBuilderHasService($service, $expected);
    }

    /**
     * @return array
     */
    public function dataProviderServices()
    {
        return [
            ['fixtures.loader.registry', 'FixturesBundle\Registry'],
        ];
    }
}
