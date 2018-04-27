<?php

namespace KRG\EasyAdminExtensionBundle\Configuration;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;
use KRG\DoctrineExtensionBundle\Entity\Sortable\SortableInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

class ActionConfigPass implements ConfigPassInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(EntityManagerInterface $entityManager, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->entityManager = $entityManager;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function process(array $backendConfig)
    {
        // Auto configurated sortable action depending on SortableInterface implementation
        foreach ($backendConfig['entities'] as &$config) {
            $classMetadata = $this->entityManager->getMetadataFactory()->getMetadataFor($config['class']);
            if ($classMetadata->getReflectionClass()->implementsInterface(SortableInterface::class)) {
                $config['sort'] = ['position' => 'ASC'];
                $config['templates']['list'] = 'KRGEasyAdminExtensionBundle:default:list.html.twig';
            } else {
                foreach ($config['list']['actions'] as $idx => &$action) {
                    if ($action['name'] === 'sort') {
                        unset($config['list']['actions'][$idx]);
                    }
                }
                unset($action);
            }
        }
        unset($config);

        if ($this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) {
            return $backendConfig;
        }

        try {
            $this->checkSecurity($backendConfig['entities']);
            $this->checkSecurity($backendConfig['list']['actions']);
            $this->checkSecurity($backendConfig['design']['menu']);
        } catch (AuthenticationCredentialsNotFoundException $exception) {
            if (php_sapi_name() !== 'cli') {
                throw $exception;
            }
            error_log('Skip AuthenticationCredentialsNotFoundException');
        }

        return $backendConfig;
    }

    private function checkSecurity(array &$config)
    {
        foreach ($config as $idx => &$_config) {
            if (isset($_config['roles']) && is_array($_config['roles']) && count($_config['roles']) > 0 && !$this->authorizationChecker->isGranted($_config['roles'])) {
                unset($config[$idx]);
                continue;
            }
            if (isset($_config['children'])) {
                $this->checkSecurity($_config['children']);
            }
        }
        unset($_config);
    }
}
