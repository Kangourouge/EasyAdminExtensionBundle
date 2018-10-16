<?php

namespace KRG\EasyAdminExtensionBundle\Configuration;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Translation\TranslatorInterface;

class MenuConfigPass implements ConfigPassInterface
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * MenuConfigPass constructor.
     *
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param TranslatorInterface $translator
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker, TranslatorInterface $translator)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->translator = $translator;
    }

    public function process(array $backendConfig)
    {
        $this->processGroup($backendConfig['design']['menu'], $backendConfig['entities']);
        $this->processMenu($backendConfig['design']['menu'], $backendConfig['entities']);
        $this->processPriority($backendConfig['design']['menu'], $backendConfig['entities']);
        $this->processMenuIndex($backendConfig['design']['menu']);

        return $backendConfig;
    }

    protected function processMenu(array &$menu, array &$entities)
    {
        foreach ($menu as $idx => &$_menu) {

            $this->processIcons($_menu, $entities);
            $this->processSecurity($_menu, $entities);

            if (isset($_menu['children'])) {
                $this->processPriority($_menu['children'], $entities);
                $this->processMenu($_menu['children'], $entities);
            }

            $this->processEmpty($menu, $entities);
        }
        unset($_menu);
    }

    protected function processIcons(array &$menu, array &$entities)
    {
        if (isset($menu['entity']) && isset($menu['icon']) && isset($entities[$menu['entity']])) {
            $entityConfig = &$entities[$menu['entity']];
            $entityConfig['icon'] = $menu['icon'];

            unset($entityConfig);
        }
    }

    /**
     * Sort menu by priority
     */
    protected function processPriority(array &$menu)
    {
        usort($menu, [$this, 'sortByPriority']);
    }

    /**
     * Merge menu groups
     */
    protected function processGroup(array &$menu, array &$entities)
    {
        $groups = [];
        foreach($menu as $idx => &$_menu) {
            if (isset($_menu['children']) && count($_menu['children']) > 0) {
                $key = $_menu['label'];
                if (!isset($groups[$_menu['label']])) {
                    $groups[$key] = &$_menu;
                } else {
                    $groups[$key]['children'] = array_merge($groups[$key]['children'], $_menu['children']);
                    $groups[$key]['priority'] = max($groups[$key]['priority'], $_menu['priority']);
                    unset($menu[$idx]);
                }
            }
        }
        unset($_menu);
    }


    /**
     * Merge menu groups
     */
    protected function processMenuIndex(array &$menu, int $parentIndex = null)
    {
        $groups = [];
        foreach($menu as $idx => &$_menu) {
            $_menu['menu_index'] = $parentIndex !== null ? $parentIndex : $idx;
            $_menu['submenu_index'] = $parentIndex !== null ? $idx : -1;
            if (isset($_menu['children']) && count($_menu['children']) > 0) {
                $this->processMenuIndex($_menu['children'], $idx);
            }
        }
        unset($_menu);
    }

    private function processSecurity(array &$menu, array &$entities)
    {
        if (isset($menu['type']) && $menu['type'] === 'entity' && isset($menu['entity']) && isset($entities[$menu['entity']])) {
            try {
                if (false === $this->authorizationChecker->isGranted('R', $entities[$menu['entity']]['class'])) {
                    unset($menu);
                }
            } catch(AuthenticationCredentialsNotFoundException $exception) {
                
            }
        }
    }

    private function processEmpty(array &$menu, array &$entities)
    {
        if (isset($menu['type']) && $menu['type'] === 'empty' && isset($menu['children']) && count($menu['children']) === 0) {
            unset($config[$idx]);
        }
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
