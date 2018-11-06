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
        foreach ($backendConfig['entities'] as $entity => &$config) {
            $this->processSort($config);
        }
        unset($config);

        try {
            $this->checkSecurityEntity($backendConfig['entities']);
        } catch (AuthenticationCredentialsNotFoundException $exception) {
            if (php_sapi_name() !== 'cli') {
                throw $exception;
            }
        }

        return $backendConfig;
    }

    private function processSort(&$config)
    {
        // Auto configurated sortable action depending on SortableInterface implementation
        $classMetadata = $this->entityManager->getMetadataFactory()->getMetadataFor($config['class']);

        $isSortable = $classMetadata->getReflectionClass()->implementsInterface(SortableInterface::class);

        $maxActions = $config['list']['max_actions'] ?? 2;
        if ($isSortable) {
            foreach ($config['list']['fields'] as $name => $field) {
                $config['list']['fields'][$name]['sortable'] = false;
            }
            $config['list']['sort'] = ["field" => "position", "direction" => "ASC"];
            $maxActions++;
        } else {
            foreach ($config['list']['actions'] as $idx => &$action) {
                if ($action['name'] === 'sort') {
                    unset($config['list']['actions'][$idx]);
                }
            }
            unset($action);
        }

        $config['list']['max_actions'] = $maxActions;

        usort($config['list']['actions'], 'KRG\EasyAdminExtensionBundle\Configuration\MenuConfigPass::sortByPriority');

        return $config;
    }

    private function checkSecurityEntity(array &$config)
    {
        foreach ($config as $idx => &$entity) {
            if (isset($entity['edit']) && isset($entity['edit']['actions'])) {
                $this->checkSecurityAction($entity['edit']['actions'], $entity['class']);
            }
            if (isset($entity['list']) && isset($entity['list']['actions'])) {
                $this->checkSecurityAction($entity['list']['actions'], $entity['class']);
            }
            if (isset($entity['show']) && isset($entity['show']['actions'])) {
                $this->checkSecurityAction($entity['show']['actions'], $entity['class']);
            }
        }
        unset($entity);
    }

    private function checkSecurityAction(array &$config, $className)
    {
        foreach ($config as $idx => &$action) {
            if (!$this->authorizationChecker->isGranted($action['name'], $className)) {
                unset($config[$idx]);
            }

            if (isset($action['children'])) {
                $this->checkSecurityAction($action['children'], $className);
            }
        }
        unset($action);
    }
}
