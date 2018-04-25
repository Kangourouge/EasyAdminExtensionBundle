<?php

namespace KRG\EasyAdminExtensionBundle\Configuration;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;
use KRG\DoctrineExtensionBundle\Entity\Sortable\SortableInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ClassMetadataConfigPass implements ConfigPassInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * ActionConfigPass constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function process(array $backendConfig)
    {
        foreach ($backendConfig['entities'] as &$config) {
            $classMetadata = $this->entityManager->getMetadataFactory()->getMetadataFor($config['class']);
            $config['class'] = $classMetadata->getName();
        }
        unset($config);
        return $backendConfig;
    }
}