<?php

namespace KRG\EasyAdminExtensionBundle\Configuration;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;
use EasyCorp\Bundle\EasyAdminBundle\Form\Guesser\MissingDoctrineOrmTypeGuesser;
use KRG\CoreBundle\Serializer\ImportNormalizer;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeGuesserInterface;

class ImportConfigPass implements ConfigPassInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var FormTypeGuesserInterface */
    private $typeGuesser;

    /**
     * FilterConfigPass constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param FormFactoryInterface $formFactory
     * @param MissingDoctrineOrmTypeGuesser $typeGuesser
     */
    public function __construct(EntityManagerInterface $entityManager, FormFactoryInterface $formFactory, MissingDoctrineOrmTypeGuesser $typeGuesser)
    {
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        $this->typeGuesser = $typeGuesser;
    }

    public function process(array $backendConfig)
    {
        foreach ($backendConfig['entities'] as &$config) {
            if (isset($config['import'])) {
                $config['import']['form_name'] = sprintf('import_%s', strtolower($config['name']));
                $config['import']['normalizer'] = ImportNormalizer::class;
            }
        }
        unset($config);

        return $backendConfig;
    }

    public function getMetadata(ClassMetadata $classMetadata, array $propertyPath)
    {
        if (count($propertyPath) === 0) {
            return [];
        }
        $fieldName = array_shift($propertyPath);

        if ($classMetadata->hasField($fieldName)) {
            $fieldMapping = $classMetadata->getFieldMapping($fieldName);
            return [$fieldMapping];
        }
        elseif ($classMetadata->hasAssociation($fieldName)) {
            $fieldMapping = $classMetadata->getAssociationMapping($fieldName);
            $fieldClassMetadata = $this->entityManager->getClassMetadata($fieldMapping['targetEntity']);
            if (count($propertyPath) === 0) {
                $propertyPath[] = 'id';
            }
            return array_merge($this->getMetadata($fieldClassMetadata, $propertyPath), [$fieldMapping]);
        }
        else {
            throw new \InvalidArgumentException;
        }

    }
}