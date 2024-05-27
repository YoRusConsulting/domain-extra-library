<?php

namespace YoRus\DomainExtraLibrary\App\Bundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class DomainExtraLibraryExtension
 */
class DomainExtraLibraryExtension extends Extension
{
    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        if ($container->getParameter('kernel.environment') === 'test') {
            $loader->load('services_test.yaml');
        }
    }
}
