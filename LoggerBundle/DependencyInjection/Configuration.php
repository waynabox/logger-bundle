<?php

namespace Waynabox\LoggerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('waynabox_logger');

        $rootNode->isRequired()
            ->children()
                ->scalarNode('logger_file_path')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('logger_exception_file_path')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
            ->end();

        return $treeBuilder;
    }
}