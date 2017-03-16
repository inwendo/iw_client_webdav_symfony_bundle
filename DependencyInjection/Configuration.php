<?php

namespace Inwendo\WebDavClientBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('inwendo_web_dav_client');
        $rootNode->children()
            ->scalarNode('endpoint')
            ->defaultValue('https://webdavgateway.service.inwendo.cloud')
            ->cannotBeEmpty()
            ->end()
            ->scalarNode('oauth_client_id')
            ->isRequired()
            ->defaultNull()
            ->cannotBeEmpty()
            ->end()
            ->scalarNode('oauth_client_secret')
            ->defaultNull()
            ->cannotBeEmpty()
            ->end()
            ->end();

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
