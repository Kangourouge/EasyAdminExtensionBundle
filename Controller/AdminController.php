<?php

namespace KRG\EasyAdminExtensionBundle\Controller;

use KRG\EasyAdminExtensionBundle\Widget\WidgetInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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
        $field = 'position';
        $getter = 'get'.ucfirst($field);
        $setter = 'set'.ucfirst($field);
        $easyadmin = $this->request->attributes->get('easyadmin');
        $entity = $easyadmin['item'];
        $currentPosition = $entity->$getter();
        $newPosition = $currentPosition + $relativePosition;
        $entity->$setter($newPosition);
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
}
