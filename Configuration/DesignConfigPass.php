<?php

namespace KRG\EasyAdminExtensionBundle\Configuration;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;

class DesignConfigPass implements ConfigPassInterface
{
    public function process(array $backendConfig)
    {
        $backendConfig = self::addCssFile('/bundles/krgeasyadminextension/css/style.css', $backendConfig);
        if ('dark' === $backendConfig['design']['color_scheme']) {
            $backendConfig = self::addCssFile('/bundles/krgeasyadminextension/css/dark.css', $backendConfig);
        }

        $backendConfig = self::addJsFile('/bundles/krgeasyadminextension/js/form-group-lock.js', $backendConfig);

        return $backendConfig;
    }

    static public function addCssFile($cssFile, $backendConfig)
    {
        $backendConfig['design']['assets']['css'][] = $cssFile;

        return $backendConfig;
    }

    static public function addJsFile($jsFile, $backendConfig)
    {
        $backendConfig['design']['assets']['js'][] = $jsFile;

        return $backendConfig;
    }
}
