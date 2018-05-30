<?php

namespace KRG\EasyAdminExtensionBundle\Configuration;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;

class MenuConfigPass implements ConfigPassInterface
{
    public function process(array $backendConfig)
    {
        $backendConfig = $this->processPriorty($backendConfig);
        $backendConfig = $this->processGroups($backendConfig);

        return $backendConfig;
    }

    /**
     * Sort menu by priority
     */
    protected function processPriorty(array $backendConfig)
    {
        usort($backendConfig['design']['menu'], [$this, 'sortByPriority']);

        return $backendConfig;
    }

    /**
     * Merge menu groups
     */
    protected function processGroups(array $backendConfig)
    {
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
