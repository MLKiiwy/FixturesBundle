<?php

namespace LaFourchette\FixturesBundle;

use LaFourchette\FixturesBundle\DependencyInjection\Compiler\EventDispatcherCompilerPass;
use LaFourchette\FixturesBundle\DependencyInjection\Compiler\RegistryCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FixturesBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegistryCompilerPass());
        $container->addCompilerPass(new EventDispatcherCompilerPass());
    }
}
