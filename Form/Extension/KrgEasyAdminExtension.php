<?php

namespace KRG\EasyAdminExtensionBundle\Form\Extension;

use EasyCorp\Bundle\EasyAdminBundle\Form\Util\LegacyFormHelper;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class KrgEasyAdminExtension extends AbstractTypeExtension
{
    /** @var Request */
    protected $request;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if (null === $this->request) {
            return;
        }

        if ($this->request->attributes->has('easyadmin')) {
            $view->vars['lock'] = $options['lock'];
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'lock' => false, // Display lock/unlock field checkbox toggler
        ]);

        $resolver->setAllowedTypes('lock', ['boolean']);
    }

    public function getExtendedType()
    {
        return LegacyFormHelper::getType('form');
    }
}
