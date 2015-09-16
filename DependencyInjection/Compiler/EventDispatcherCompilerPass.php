<?php

namespace LaFourchette\FixturesBundle\DependencyInjection\Compiler;

use LaFourchette\FixturesBundle\Event\FixturesEventDispatcher;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class EventDispatcherCompilerPass implements CompilerPassInterface
{
    const EVENT_DISPATCHER_ID = 'fixtures.event_dispatcher';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::EVENT_DISPATCHER_ID)) {
            return;
        }

        $eventDispatcherDefinition = $container->getDefinition(self::EVENT_DISPATCHER_ID);

        foreach ($container->findTaggedServiceIds(FixturesEventDispatcher::SUBSCRIBE_TAG) as $id => $attributes) {
            $eventDispatcherDefinition->addMethodCall('addSubscriber', array(new Reference($id)));
        }
    }
}
