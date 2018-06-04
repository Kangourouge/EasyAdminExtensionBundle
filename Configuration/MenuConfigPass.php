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
        $groups = [];
        foreach ($menuConfig as $i => $itemConfig) {
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
                unset($menuConfig[$i]);
            }
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
