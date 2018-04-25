<?php

namespace KRG\EasyAdminExtensionBundle;

use KRG\EasyAdminExtensionBundle\DependencyInjection\Compiler\EasyAdminCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class KRGEasyAdminExtensionBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new EasyAdminCompilerPass());
    }
}
