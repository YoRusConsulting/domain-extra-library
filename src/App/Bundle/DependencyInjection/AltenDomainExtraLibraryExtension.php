<?php

namespace AppInWeb\DomainExtraLibrary\App\Bundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class AppInWebDomainExtraLibraryExtension
 */
class AppInWebDomainExtraLibraryExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        if ($container->getParameter('kernel.environment') === 'test') {
            $loader->load('services_test.yaml');
        }
    }
}
