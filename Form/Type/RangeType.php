<?php

namespace KRG\EasyAdminExtensionBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RangeType extends AbstractType
{
    /** @var array */
    public static $types = [IntegerType::class, NumberType::class, DateType::class, TimeType::class, DateTimeType::class];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('min', $options['entry_type'], array_merge($options['entry_options'], ['label' => 'from']))
                ->add('max', $options['entry_type'], array_merge($options['entry_options'], ['label' => 'to']));

        $builder->addModelTransformer(new CallbackTransformer(
            function($value) {
                if ($value === null) {
                    return ['min' => null, 'max' => null];
                }
                return $value;
            },
            function($value) {
                if ($value['min'] === null && $value['max'] === null) {
                    return null;
                }
                return $value;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('entry_options', []);
        $resolver->setRequired(['entry_type', 'entry_options']);
        $resolver->setAllowedTypes('entry_type', 'string');
        $resolver->setAllowedTypes('entry_options', 'array');
    }

    public function getBlockPrefix()
    {
        return 'form';
    }
}