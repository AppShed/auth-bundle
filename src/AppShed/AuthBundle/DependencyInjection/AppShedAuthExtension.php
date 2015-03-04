<?php

namespace AppShed\AuthBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AppShedAuthExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('app_shed_auth.api', $config['api']);
        $container->setParameter('app_shed_auth.cookie_name', $config['cookie_name']);

        $args = [
            new Reference('app_shed_auth.client')
        ];

        if (array_key_exists('cache_service', $config)) {
            $args[] = new Reference($config['cache_service']);
        }

        $container->addDefinitions([
            'app_shed_auth.cookie_user_provider' => new Definition('AppShed\AuthBundle\Security\CookieUserProvider', $args)
        ]);
    }
}
