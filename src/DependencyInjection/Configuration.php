<?php

namespace Martenasoft\Bundlemaker\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('martenasoft_bundlemaker');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('some_param')->defaultValue('default value')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

