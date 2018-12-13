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

        if (count($options['execute_actions']) > 0) {
            $builder
                ->add(
                    'action', ChoiceType::class, [
                        'required'    => false,
                        'label'       => false,
                        'placeholder' => 'form.selection.action',
                        'choices'     => array_column($options['execute_actions'], 'name', 'label'),
                        'choice_translation_domain' => 'admin'
                    ]
                )
                ->add('execute', SubmitType::class, [
                    'label' => 'action.execute',
                    'attr' => ['class' => 'btn-primary']
                ]);
        }

        if (count($options['export_formats']) > 0) {
            $sheets = array_merge(array_flip(array_column($options['export_sheets'],'label')));
            $builder
                ->add(
                    'format', ChoiceType::class, [
                        'label'         => false,
                        'required'      => false,
                        'placeholder'   => 'form.selection.format',
                        'choices'       => array_column($options['export_formats'], 'name', 'label'),
                        'choice_translation_domain' => 'admin'
                    ]
                )
                ->add(
                    'sheet', ChoiceType::class, [
                        'label'         => false,
                        'required'      => false,
                        'placeholder'   => 'All',
                        'choices'       => $sheets,
                        'choice_translation_domain' => 'admin'
                    ]
                )
                ->add('export', SubmitType::class, [
                    'label' => 'action.export',
                    'attr' => ['class' => 'btn-info btn-export']
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('translation_domain', 'admin');
        $resolver->setRequired(['entities', 'execute_actions', 'export_formats', 'export_sheets']);
        $resolver->setAllowedTypes('entities', ['array', \ArrayIterator::class]);
        $resolver->setAllowedTypes('execute_actions', 'array');
        $resolver->setAllowedTypes('export_formats', 'array');
        $resolver->setAllowedTypes('export_sheets', 'array');
    }

    public function getBlockPrefix()
    {
        return 'selection';
    }
}