<?php

namespace KRG\EasyAdminExtensionBundle\Configuration;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;
use EasyCorp\Bundle\EasyAdminBundle\Form\Guesser\MissingDoctrineOrmTypeGuesser;
use KRG\EasyAdminExtensionBundle\Filter\FilterQueryBuilder;
use KRG\EasyAdminExtensionBundle\Form\Type\FilterType;
use KRG\EasyAdminExtensionBundle\Form\Type\RangeType;
use KRG\EasyAdminExtensionBundle\IO\Export;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeGuesserInterface;

class ExportConfigPass implements ConfigPassInterface
{
    public function process(array $backendConfig)
    {
        foreach ($backendConfig['entities'] as &$config) {
            if (isset($config['export'])) {
                foreach($config['export']['sheets'] as &$sheet) {
                    foreach($sheet['fields'] as &$field) {
                        if (is_string($field)) {
                            $field = ['property_path' => $field];
                            $field['label'] = $field['label'] ?? $field['property_path'];
                            $field['options'] = $field['options'] ?? [];
                        }
                    }
                    unset($field);
                }
                unset($sheet);
            }
        }
        unset($config);

        return $backendConfig;
    }
}