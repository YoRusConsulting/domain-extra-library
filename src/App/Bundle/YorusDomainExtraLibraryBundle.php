<?php

namespace YoRus\DomainExtraLibrary\App\Bundle;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use YoRus\DomainExtraLibrary\App\Bundle\DependencyInjection\DomainExtraLibraryExtension;
use YoRus\DomainExtraLibrary\App\Bundle\DependencyInjection\YorusDomainExtraLibraryExtension;

/**
 * Class DomainExtraLibraryBundle
 */
class YorusDomainExtraLibraryBundle extends AbstractBundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new YorusDomainExtraLibraryExtension();
    }

    public function getAlias(): string
    {
        return 'yorus_domain_extra_library';
    }
}
