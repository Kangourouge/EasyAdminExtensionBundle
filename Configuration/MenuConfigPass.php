<?php

namespace KRG\EasyAdminExtensionBundle\Configuration;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;

class MenuConfigPass implements ConfigPassInterface
{
    public function process(array $backendConfig)
    {
        // Merge group menu
        $groups = [];
        foreach ($backendConfig['design']['menu'] as $i => $itemConfig) {
            if (empty($itemConfig['children']) || !isset($itemConfig['group'])) {
                continue;
            }

            $key = array_search($itemConfig['group'], $groups);
            if (false === $key) {
                $groups[$i] = $itemConfig['group'];
            } else {
                foreach ($itemConfig['children'] as $child) { // Add similar group child to parent children
                    $backendConfig['design']['menu'][$key]['children'][] = $child;
                }
                unset($backendConfig['design']['menu'][$i]);
            }
        }

        return $backendConfig;
    }
}
