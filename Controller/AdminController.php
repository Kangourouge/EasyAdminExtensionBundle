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
        $id = $this->request->query->get('id');
        $relativePosition = $this->request->query->get('relativePosition');
        $field = 'position';
        $getter = 'get'.ucfirst($field);
        $setter = 'set'.ucfirst($field);

        $easyadmin = $this->request->attributes->get('easyadmin');
        $entity = $easyadmin['item'];
        $currentPosition = $entity->$getter();
        $newPosition = $currentPosition + $relativePosition;

        // Find impacted items
        $qb = $this->em->createQueryBuilder()
           ->select('e')
           ->from($easyadmin['entity']['class'], 'e')
           ->where('e.position >= :floor')
           ->andWhere('e.position <= :top')
           ->andWhere('e.id != :id')
           ->setParameters([
               'floor' => $currentPosition < $newPosition ? $currentPosition : $newPosition,
               'top'   => $currentPosition > $newPosition ? $currentPosition : $newPosition,
               'id'    => $id,
           ]);

        // Move other items
        foreach ($qb->getQuery()->getResult() as $entry) {
            $entry->setPosition($entry->$getter() + (($entry->$getter() < $newPosition || ($entry->$getter() === $newPosition && $newPosition > $currentPosition)) ? -1 : 1));
        }
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
