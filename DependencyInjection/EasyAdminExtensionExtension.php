<?php

namespace KRG\EasyAdminExtensionBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Yaml;

class EasyAdminExtensionExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->injectParameter('title', $container, $config);
        $this->injectParameter('default_title', $container, $config);
        $container->setParameter('krg_cms.blocks', $this->loadBlocks($config));

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    private function loadBlocks($config)
    {
        $blocks = [];

        if (isset($config['blocks_path'])) {
            $finder = new Finder();
            foreach ($config['blocks_path'] as $path) {
                if (is_dir($path)) {
                    $finder->files()->in($path)->name('*.yml');
                    foreach ($finder as $file) {
                        $this->loadBlockConfig($file, $blocks);
                    }
                } else {
                    $this->loadBlockConfig($path, $blocks);
                }
            }
        }

        return $blocks;
    }

    private function loadBlockConfig($file, &$blocks = [])
    {
        foreach (Yaml::parseFile($file) as $key => $yml) {
            if (isset($yml['template'])) {
                $blocks[$key] = $yml;
            }
        }

        return $blocks;
    }

    private function injectParameter($name, ContainerBuilder $container, $config)
    {
        $container->setParameter(sprintf('krg_cms.%s', $name), isset($config[$name]) ? $config[$name] : null);
    }

    public function getAlias()
    {
        return 'krg_cms';
    }
}
