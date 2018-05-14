<?php

namespace KRG\EasyAdminExtensionBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class KRGEasyAdminExtensionExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        if (isset($config['dashboard']['widgets'])) {
            $container->setParameter('krg_easyadmin_dashboard_widgets', $config['dashboard']['widgets']);
        }

        $container->setParameter('krg_easyadmin_google_analytics_id', $config['google_analytics']['client_id']);
    }
}
