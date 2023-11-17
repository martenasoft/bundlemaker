<?php

namespace Martenasoft\Bundlemaker\DependencyInjection;

use Martenasoft\Makebundle\Command\BundleCommand;
use Martenasoft\Makebundle\Command\MakeBundleCommand;
use Martenasoft\Makebundle\Maker\BundleMaker;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class MartenasoftBundlemakerExtension extends Extension
{
    public function loadExtension(
        array                 $config,
        ContainerConfigurator $containerConfigurator,
        ContainerBuilder      $containerBuilder): void
    {
        $containerConfigurator->import(__DIR__ . '/../../config/services.yaml');

    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator (__DIR__ . '/../../config')
        );

        $loader->load('services.yaml');
    }
}

