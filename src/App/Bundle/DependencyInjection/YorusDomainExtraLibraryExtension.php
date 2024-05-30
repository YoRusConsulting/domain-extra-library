<?php

namespace YoRus\DomainExtraLibrary\App\Bundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class YorusDomainExtraLibraryExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter('yorus_domain_extra_library.namespace', $config['namespace']);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../../../../config'));
        $loader->load('configuration.xml');

        $loaderJson = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../../../../config'));
        $loaderJson->load('services.yaml');
    }
}
