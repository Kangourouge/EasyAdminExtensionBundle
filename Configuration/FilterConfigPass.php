<?php

namespace KRG\EasyAdminExtensionBundle\Configuration;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;
use EasyCorp\Bundle\EasyAdminBundle\Form\Guesser\MissingDoctrineOrmTypeGuesser;
use KRG\EasyAdminExtensionBundle\Filter\FilterQueryBuilder;
use KRG\EasyAdminExtensionBundle\Form\Type\RangeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeGuesserInterface;

class FilterConfigPass implements ConfigPassInterface
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
            if (isset($config['filter'])) {
                if (!isset($config['filter']['form_type'])) {
                    $classMetadata = $this->entityManager->getMetadataFactory()->getMetadataFor($config['class']);
                    foreach($config['filter']['fields'] as $idx => &$field) {
                        if (is_string($field)) {
                            $field = ['property_path' => $field];
                        }
                        $propertyPath = preg_split('/\./', $field['property_path']);
                        $metadata = array_reverse($this->getMetadata($classMetadata, $propertyPath));

                        $targetEntity = $config['class'];

                        $mapping = reset($metadata);
                        if (count($metadata) > 1) {
                            foreach ($metadata as $_idx => &$_metadata) {
                                if (isset($_metadata['sourceEntity'])) {
                                    $mapping = $_metadata;
                                    $targetEntity = $_metadata['sourceEntity'];
                                    $_metadata['entityAlias'] = sprintf('filter_entity_%d_%d', $idx, $_idx);
                                }
                            }
                            unset($_metadata);
                        }

                        $typeGuess = $this->typeGuesser->guessType($targetEntity, $mapping['fieldName']);

                        $field['name'] = sprintf('f%d', $idx);
                        $field['metadata'] = $metadata;

                        $field['type'] = $field['type'] ?? $typeGuess->getType();

                        $field['type_options'] = $field['type_options'] ?? $typeGuess->getOptions();
                        $field['type_options']['label'] = $field['type_options']['label'] ?? $field['property_path'];
                        $field['type_options']['required'] = false;

                        if (!isset($field['dql_callback'])) {
                            $field['dql_callback'] = [FilterQueryBuilder::class, 'addQueryBuilder'];

                            if (in_array($field['type'], [TextType::class, TextareaType::class])) {
                                $field['dql_callback'] = [FilterQueryBuilder::class, 'addQueryBuilderText'];
                            }
                            else if (in_array($field['type'], [EntityType::class, ChoiceType::class])) {
                                $field['dql_callback'] = [FilterQueryBuilder::class, 'addQueryBuilderChoice'];
                            }
                            else if (in_array($field['type'], RangeType::$types)) {
                                $field['dql_callback'] = [FilterQueryBuilder::class, 'addQueryBuilderRange'];
                            }
                        }
                    }
                    unset($field);
                }

                $config['filter']['form_name'] = sprintf('filter_%s', strtolower($config['name']));
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