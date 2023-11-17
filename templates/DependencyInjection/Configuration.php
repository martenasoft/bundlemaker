<?php

namespace __REPLACE_NAMESPACE__\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('__REPLACE_NAMESPACE_FOR_CONFIG_NAME__');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('some_param')->defaultValue('default value')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

