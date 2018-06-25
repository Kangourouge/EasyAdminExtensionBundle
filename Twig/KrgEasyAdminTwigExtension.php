<?php

namespace KRG\EasyAdminExtensionBundle\Twig;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigManager;
use EasyCorp\Bundle\EasyAdminBundle\Router\EasyAdminRouter;
use EasyCorp\Bundle\EasyAdminBundle\Twig\EasyAdminTwigExtension;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class KrgEasyAdminTwigExtension extends EasyAdminTwigExtension
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    public function __construct(ConfigManager $configManager, PropertyAccessor $propertyAccessor, EasyAdminRouter $easyAdminRouter, $debug = false, $logoutUrlGenerator, AuthorizationCheckerInterface $authorizationChecker)
    {
        parent::__construct($configManager, $propertyAccessor, $easyAdminRouter, $debug, $logoutUrlGenerator);
        $this->authorizationChecker = $authorizationChecker;
    }

    public function getActionsForItem($view, $entityName)
    {
        $actions = parent::getActionsForItem($view, $entityName);

        if ('list' !== $view) {
            return $actions;
        }

        usort($actions, function ($a, $b) {
            if (!array_key_exists('priority', $a)) {
                $a['priority'] = 0;
            }

            if (!array_key_exists('priority', $b)) {
                $b['priority'] = 0;
            }

            return $a['priority'] - $b['priority'];
        });

        $dropdownGroups = [];
        $dropdownGlobal = [];
        foreach ($actions as $key => $action) {
            if (isset($action['roles']) && false === $this->authorizationChecker->isGranted($action['roles'])) {
                unset($actions[$key]);
                continue;
            }

            if (isset($action['menu_dropdown'])) {
                if (isset($action['menu_dropdown']['name'])) {
                    $dropdownGroups[$action['menu_dropdown']['name']][] = $action;
                } else {
                    $dropdownGlobal[$key] = $action;
                }
                unset($actions[$key]);
            }
        }

        if (0 === count($actions) && 0 === count($dropdownGroups) && 0 === count($dropdownGlobal)) {
            return [];
        }

        return [
            'actions'        => $actions,
            'dropdownGroups' => $dropdownGroups,
            'dropdownGlobal' => $dropdownGlobal,
        ];
    }
}
