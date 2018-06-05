<?php

namespace KRG\EasyAdminExtensionBundle\Configuration;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;

class MenuConfigPass implements ConfigPassInterface
{
    public function process(array $backendConfig)
    {
        $menuConfig = $backendConfig['design']['menu'];

        $menuConfig = $this->processPriority($menuConfig);
        $menuConfig = $this->processGroups($menuConfig);
        $menuConfig = $this->processMenuIndex($menuConfig);

        foreach ($menuConfig as $i => $itemConfig) {
            $itemConfig['menu_index'] = $i;

            if (empty($itemConfig['children'])) {
                continue;
            }

            $submenuConfig = $itemConfig['children'];
            $submenuConfig = $this->processMenuIndex($submenuConfig, $i);
            $menuConfig[$i]['children'] = $submenuConfig;
        }

        $backendConfig['design']['menu'] = $menuConfig;

        return $backendConfig;
    }

    /**
     * Reorder menu_index and submenu_index
     */
    protected function processMenuIndex(array $menuConfig, $parentItemIndex = -1)
    {
        foreach ($menuConfig as $i => $itemConfig) {
            $itemConfig['menu_index'] = ($parentItemIndex === -1) ? $i : $parentItemIndex;
            $itemConfig['submenu_index'] = ($parentItemIndex === -1) ? -1 : $i;
            $menuConfig[$i] = $itemConfig;
        }

        return $menuConfig;
    }

    /**
     * Sort menu by priority
     */
    protected function processPriority(array $menuConfig)
    {
        usort($menuConfig, [$this, 'sortByPriority']);

        return $menuConfig;
    }

    /**
     * Merge menu groups
     */
    protected function processGroups(array $menuConfig)
    {
        // Index menu items by groups name
        $groups = [];
        foreach ($menuConfig as $i => $itemConfig) {
            if (empty($itemConfig['children']) || !isset($itemConfig['group'])) {
                continue;
            }

            $groups[$itemConfig['group']][] = $i;
        }

        // Merge children from corresponding groups, unset useless menu items
        foreach ($groups as $indexes) {
            $children = [];
            foreach ($indexes as $i) {
                $children = array_merge($children, $menuConfig[$i]['children']);

                if ($i !== $indexes[0]) {
                    unset($menuConfig[$i]);
                }
            }

            $menuConfig[$indexes[0]]['children'] = $children;
        }

        return $menuConfig;
    }

    static function sortByPriority($a, $b)
    {
        $a = $a['priority'] ?? 0;
        $b = $b['priority'] ?? 0;

        if ($a == $b) {
            return 0;
        }

        return ($a > $b) ? -1 : 1;
    }
}
