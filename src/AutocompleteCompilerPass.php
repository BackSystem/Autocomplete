<?php

namespace BackSystem\Autocomplete;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @template T of object
 */
final class AutocompleteCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $servicesMap = [];

        foreach ($container->findTaggedServiceIds('autocomplete.injectable', true) as $serviceId => $tag) {
            /** @var ApiInterface<T> $instance */
            $instance = new $serviceId();

            $servicesMap[$instance->getUrl()] = new Reference($serviceId);
        }

        $definition = $container->findDefinition('autocomplete.registry');
        $definition->setArgument(0, ServiceLocatorTagPass::register($container, $servicesMap));
    }
}
