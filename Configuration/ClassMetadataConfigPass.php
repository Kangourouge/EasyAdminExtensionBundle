<?php

namespace KRG\EasyAdminExtensionBundle\Configuration;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;

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

            foreach($config['list']['fields'] as &$field) {
                if (is_string($field) && $classMetadata->hasField($field) && substr($classMetadata->getTypeOfField($field), -5) === '_enum' ) {
                    $field = [
                        'type' => 'enum',
                        'property' => $field,
                        'translation_domain' => $classMetadata->getTypeOfField($field),
                        'template' => '@KRGEasyAdminExtension/default/field_enum.html.twig'
                    ];
                }
            }
            unset($field);
        }
        unset($config);

        return $backendConfig;
    }
}