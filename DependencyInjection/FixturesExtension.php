<?php

namespace LaFourchette\FixturesBundle\DependencyInjection;

use LaFourchette\FixturesBundle\Registry;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class FixturesExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        // Build loaders
        foreach ($config as $entityManagerName => $loaderConfig) {
            $entityManagerIdentifier = sprintf('doctrine.orm.%s_entity_manager', $entityManagerName);
            $loaderDefinition = new Definition('LaFourchette\FixturesBundle\Loader\FixturesLoader');
            $loaderDefinition->addArgument(new Reference($entityManagerIdentifier));
            $loaderDefinition->addArgument(new Reference('kernel'));
            $loaderDefinition->addMethodCall('setGroups', [$loaderConfig['groups']]);
            $loaderDefinition->addMethodCall('setDependencies', [$loaderConfig['dependencies']]);
            $loaderDefinition->addMethodCall('setProviderClasses', [$loaderConfig['providerClasses']]);
            if ($loaderConfig['fixturesDataProcessor']) {
                $loaderDefinition->addMethodCall('setFixturesDataProcessor', [new Reference($loaderConfig['fixturesDataProcessor'])]);
            }
            $loaderDefinition->addTag(Registry::LOADER_TAG, ['name' => $entityManagerName]);
            $container->setDefinition('fixtures.loader.'.$entityManagerName, $loaderDefinition);
        }
    }
}
