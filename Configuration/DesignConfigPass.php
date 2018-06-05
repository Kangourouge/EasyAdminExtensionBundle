<?php

namespace KRG\EasyAdminExtensionBundle\Configuration;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;

class DesignConfigPass implements ConfigPassInterface
{
    public function process(array $backendConfig)
    {
        $backendConfig = $this->processKrgCss($backendConfig);

        return $backendConfig;
    }

    private function processKrgCss(array $backendConfig)
    {
        $backendConfig['design']['assets']['css'][] = '/bundles/krgeasyadminextension/css/style.css';
        if ('dark' === $backendConfig['design']['color_scheme']) {
            $backendConfig['design']['assets']['css'][] = '/bundles/krgeasyadminextension/css/dark.css';
        }

        return $backendConfig;
    }
}
