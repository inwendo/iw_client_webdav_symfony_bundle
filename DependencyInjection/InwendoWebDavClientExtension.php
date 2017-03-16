<?php

namespace Inwendo\WebDavClientBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class InwendoWebDavClientExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $endpoint = $config['endpoint'];
        $oauth_client_id = $config['oauth_client_id'];
        $oauth_client_secret = $config['oauth_client_secret'];

        $container->setParameter('inwendo_web_dav_client.endpoint', $endpoint);
        $container->setParameter('inwendo_web_dav_client.oauth_client_id', $oauth_client_id);
        $container->setParameter('inwendo_web_dav_client.oauth_client_secret', $oauth_client_secret);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
