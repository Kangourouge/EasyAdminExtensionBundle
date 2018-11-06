<?php

namespace KRG\EasyAdminExtensionBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EasyAdminFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $config = $options['config'];
        foreach($config['filter']['fields'] as $idx => $field) {
            if (in_array($field['type'], [TextType::class, TextareaType::class])) {
                $field['type'] = TextType::class;
                $field['type_options']['attr'] = ['placeholder' => $field['type_options']['label']];
            }
            else if ($field['type'] === CheckboxType::class) {
                $field['type'] = ChoiceType::class;
                $field['type_options']['choices'] = ['Yes' => true, 'No' => false];
                $field['type_options']['placeholder'] = 'All';
            }
            else if (in_array($field['type'], [EntityType::class, ChoiceType::class])) {
                $field['type_options']['multiple'] = true;
                $field['type_options']['attr'] = [
                    'class' => 'form-control select2-hidden-accessible',
                    'placeholder' => 'Select',
                    'data-widget' => 'select2',
                    'aria-hidden' => 'true'
                ];
            }
            else if (in_array($field['type'], RangeType::$types)) {
                switch($field['type']) {
                    case DateType::class:
                        $field['type_options']['widget'] = 'single_text';
                        $field['type_options']['format'] = 'yyyy-MM-dd';
                        $field['type_options']['attr']['class'] = 'datetimepicker';
                        break;
                    case DateTimeType::class:
                        $field['type_options']['widget'] = 'single_text';
                        $field['type_options']['format'] = 'yyyy-MM-dd H:i';
                        $field['type_options']['attr']['class'] = 'datetimepicker';
                        break;
                }

                $field['type_options'] = [
                    'entry_type' => $field['type'],
                    'entry_options' => $field['type_options'],
                    'label' => $field['property_path'],
                    'required' => false
                ];

                $field['type'] = RangeType::class;
            }

            $builder->add($field['name'], $field['type'], $field['type_options']);
        }

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $event->setData(!isset($data['reset']) ? array_filter($data) : []);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('csrf_protection', false);
        $resolver->setRequired(['config']);
        $resolver->setAllowedTypes('config', 'array');
    }
}
