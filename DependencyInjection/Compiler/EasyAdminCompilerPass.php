<?php
namespace KRG\EasyAdminExtensionBundle\DependencyInjection\Compiler;

use KRG\EasyAdminExtensionBundle\Controller\AdminController;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class EasyAdminCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->findDefinition(AdminController::class);
        $taggedServices = $container->findTaggedServiceIds('krg.easyadmin.widget');
        foreach ($taggedServices as $id => $tagAttributes) {
            $definition->addMethodCall('addWidget', [new Reference($id), $id]);
        }
    }
}
