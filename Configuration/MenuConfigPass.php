<?php

namespace KRG\EasyAdminExtensionBundle\Configuration;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

class MenuConfigPass implements ConfigPassInterface
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function process(array $backendConfig)
    {
        $menuConfig = $backendConfig['design']['menu'];

        try {
            $this->checkSecurity($menuConfig);
            $this->checkSecurityMenu($menuConfig, $backendConfig['entities']);
        } catch (AuthenticationCredentialsNotFoundException $exception) {
            if (php_sapi_name() !== 'cli') {
                throw $exception;
            }
        }

        $menuConfig = $this->processPriority($menuConfig);
        $menuConfig = $this->processGroups($menuConfig);
        $menuConfig = array_values($menuConfig);

        foreach ($menuConfig as $i => $itemConfig) {
            $itemConfig['menu_index'] = $i;

            if (empty($itemConfig['children'])) {
                continue;
            }

            $submenuConfig = $itemConfig['children'];
            $submenuConfig = $this->processMenuIndex($submenuConfig, $i);
            $menuConfig[$i]['children'] = $submenuConfig;
        }

        $menuConfig = $this->processMenuIndex($menuConfig);

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

    private function checkSecurity(array &$config)
    {
        foreach ($config as $idx => &$menu) {
            if (isset($menu['roles']) && is_array($menu['roles']) && count($menu['roles']) > 0 && !$this->authorizationChecker->isGranted($menu['roles'])) {
                unset($config[$idx]);
                continue;
            }
            if (isset($menu['children'])) {
                $this->checkSecurity($menu['children']);
            }
        }
        unset($menu);
    }

    private function checkSecurityMenu(array &$config, $entities)
    {
        foreach ($config as $idx => &$menu) {
            if (isset($menu['children'])) {
                $this->checkSecurityMenu($menu['children'], $entities);
            }

            if ($menu['type'] == 'empty' && empty($menu['children'])) {
                unset($config[$idx]);
            }

            if ($menu['type'] === 'entity' && isset($menu['entity']) && isset($entities[$menu['entity']])) {
                if (false === $this->authorizationChecker->isGranted('R', $entities[$menu['entity']]['class'])) {
                    unset($config[$idx]);
                }
            }
        }
        unset($menu);
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
