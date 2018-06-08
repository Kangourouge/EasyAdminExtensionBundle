<?php

namespace KRG\EasyAdminExtensionBundle\Controller;

use KRG\EasyAdminExtensionBundle\Filter\FilterListener;
use KRG\EasyAdminExtensionBundle\Widget\WidgetInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends \EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController
{
    /** @var array */
    protected $dashboardWidgets;

    public function __construct(array $dashboardWidgets = [])
    {
        $this->dashboardWidgets = $dashboardWidgets;
    }

    public function addWidget(WidgetInterface $widget)
    {
        $this->dashboardWidgets[] = $widget;
    }

    /**
     * @Route("/dashboard", name="admin_dashboard")
     */
    public function dashboardAction()
    {
        return $this->render('KRGEasyAdminExtensionBundle:default:dashboard.html.twig', [
            'config'  => $this->config,
            'widgets' => $this->dashboardWidgets,
        ]);
    }

    /**
     * Sortable action
     */
    protected function sortAction()
    {
        $relativePosition = $this->request->query->get('relativePosition');
        $easyadmin = $this->request->attributes->get('easyadmin');
        $entity = $easyadmin['item'];

        if (false === method_exists($entity, 'getPosition')) {
            throw new \Exception(sprintf('%s entity must implement getPosition method', $easyadmin['entity']['class']));
        }

        if (false === method_exists($entity, 'setPosition')) {
            throw new \Exception(sprintf('%s entity must implement setPosition method', $easyadmin['entity']['class']));
        }

        $entity->setPosition($entity->getPosition() + $relativePosition);
        $this->em->flush();

        return new Response();
    }

    /**
     * Clone action
     */
    protected function cloneAction()
    {
        $easyadmin = $this->request->attributes->get('easyadmin');
        $entity = $easyadmin['item'];

        try {
            $clone = clone $entity;
            $this->em->persist($clone);
            $this->em->flush();
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf('The "%s" entity is not clonable.', $easyadmin['entity']['class']));
        }

        return $this->redirectToRoute('easyadmin', [
            'action' => 'edit',
            'id'     => $clone->getId(),
            'entity' => $this->request->query->get('entity'),
        ]);
    }

    protected function renderTemplate($actionName, $templatePath, array $parameters = array())
    {
        if (    ($actionName === 'list' || $actionName === 'search')
            &&  ($filterForm = $this->get(FilterListener::class)->getForm()) instanceof FormInterface) {
            $parameters['form_filter'] = $filterForm->createView();
        }
        return parent::renderTemplate($actionName, $templatePath, $parameters);
    }
}
