<?php

namespace LaFourchette\FixturesBundle\DependencyInjection\Compiler;

use LaFourchette\FixturesBundle\Registry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegistryCompilerPass implements CompilerPassInterface
{
    const REGISTRY_ID = 'fixtures.loader.registry';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::REGISTRY_ID)) {
            return;
        }

        $registryDefinition = $container->getDefinition(self::REGISTRY_ID);

        foreach ($container->findTaggedServiceIds(Registry::LOADER_TAG) as $id => $attributes) {
            if (!array_key_exists('name', $attributes[0])) {
                throw new \RuntimeException(sprintf(
                    'Tag "%s" for service "%s" must have a `name` attribute',
                    Registry::LOADER_TAG,
                    $id
                ));
            }

            $registryDefinition->addMethodCall('addLoader', [$attributes[0]['name'], new Reference($id)]);
        }
    }
}
