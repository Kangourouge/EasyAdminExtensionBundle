<?php
namespace KRG\EasyAdminExtensionBundle\DependencyInjection\Compiler;

use KRG\EasyAdminExtensionBundle\Controller\DashboardController;
use KRG\EasyAdminExtensionBundle\Twig\ToolbarExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EasyAdminCompilerPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container)
    {
        $definition = $container->findDefinition(DashboardController::class);
        $references = $this->findAndSortTaggedServices('krg.easyadmin.widget', $container);
        foreach ($references as $reference) {
            $definition->addMethodCall('addWidget', [$reference]);
        }

        $definition = $container->findDefinition(ToolbarExtension::class);
        $references = $this->findAndSortTaggedServices('krg.easyadmin.toolbar', $container);
        foreach ($references as $reference) {
            $definition->addMethodCall('addToolbar', [$reference]);
        }
    }
}
