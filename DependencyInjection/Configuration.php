<?php

namespace EMS\AuthenticationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        /** @var $rootNode ArrayNodeDefinition */
        $rootNode = $treeBuilder->root('ems_authentication');

        $rootNode
            ->children()
                ->scalarNode('redirect_url')->isRequired()->end()
                ->scalarNode('post_logout_redirect_url')->isRequired()->end()
                ->scalarNode('client_id')->isRequired()->end()
                ->scalarNode('client_secret')->isRequired()->end()
                ->scalarNode('authorize_url')->isRequired()->end()
                ->scalarNode('realm')->defaultValue('master')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
