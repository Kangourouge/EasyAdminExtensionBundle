<?php

namespace KRG\EasyAdminExtensionBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use KRG\DoctrineExtensionBundle\Form\DataTransformer\FilterDataTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var RequestStack */
    private $request;

    /** @var SessionInterface */
    private $session;

    /**
     * FilterType constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $request
     * @param SessionInterface $session
     */
    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack, SessionInterface $session)
    {
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->session = $session;
    }

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
                $field['type_options']['choices'] = ['Yes' => 1, 'No' => 0];
                $field['type_options']['placeholder'] = 'All';
            }
            else if (in_array($field['type'], [EntityType::class, ChoiceType::class])) {
                $field['type_options']['multiple'] = true;
                $field['type_options']['attr'] = [
                    'placeholder' => 'Select'
                ];
            }
            else if (in_array($field['type'], RangeType::$types)) {
                $field['type_options'] = [
                    'entry_type' => $field['type'],
                    'entry_options' => $field['type_options'],
                    'label' => $field['property_path'],
                    'required' => false
                ];

                $field['type'] = RangeType::class;
            }

            $builder->add($idx, $field['type'], $field['type_options']);
        }

        $builder->addModelTransformer(new FilterDataTransformer($this->entityManager, $config['filter']['fields']));
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    /**
     * @return string
     */
    public function getSessionKey(array $config)
    {
        return sprintf('easy_admin_%s', $config['filter']['form_name']);
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        $config = $event->getForm()->getConfig()->getOption('config');
        $data = $event->getData() ?: $this->session->get($this->getSessionKey($config), []);
        $event->setData($data);
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $config = $event->getForm()->getConfig()->getOption('config');

        if (isset($data['reset'])) {
            $data = [];
        }

        unset($data['filter']);

        $event->setData($data);

        $this->session->set($this->getSessionKey($config), $data);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('csrf_protection', false);
        $resolver->setRequired(['config']);
        $resolver->setAllowedTypes('config', 'array');
    }
}
