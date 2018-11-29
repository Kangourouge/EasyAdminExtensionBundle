<?php

namespace KRG\EasyAdminExtensionBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SelectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('entities', ChoiceType::class, [
            'choices'      => $options['entities'],
            'choice_name'  => function ($entity) { return $entity->getId(); },
            'choice_label' => function ($entity) { return $entity->getId(); },
            'expanded'     => true,
            'multiple'     => true,
        ]);

        if (count($options['actions']) > 0) {

            $actions = array_map(function(array $action){ return $action['label']; }, $options['actions']);

            $builder
                ->add(
                    'action', ChoiceType::class, [
                        'required'    => false,
                        'label'       => false,
                        'placeholder' => 'Select an action',
                        'choices'     => $actions
                    ]
                )
                ->add('submit', SubmitType::class);
        }

        if ($options['allow_export']) {
            $builder->add('export', SubmitType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['actions', 'entities', 'allow_import', 'allow_export']);
        $resolver->setAllowedTypes('actions', 'array');
        $resolver->setAllowedTypes('entities', ['array', \ArrayIterator::class]);
        $resolver->setAllowedTypes('allow_import', 'boolean');
        $resolver->setAllowedTypes('allow_export', 'boolean');
    }
}