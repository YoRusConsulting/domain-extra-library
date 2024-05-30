<?php

namespace YoRus\DomainExtraLibrary\App\Bundle\DependencyInjection;

use Psr\Log\LogLevel;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('yorus_domain_extra_library');
        $rootNode = $treeBuilder->getRootNode();
        
        $rootNode
            ->children()
                ->scalarNode('namespace')->defaultValue('App\\')->end()
            ->end();

        return $treeBuilder;
    }

    /**
     * @return string[]
     *
     * @throws \ReflectionException
     */
    private function getPSR3LogLevels()
    {
        return (new \ReflectionClass(LogLevel::class))->getConstants();
    }
}