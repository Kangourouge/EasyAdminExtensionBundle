<?php

namespace KRG\EasyAdminExtensionBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends BaseAdminController
{
    /**
     * Replace list template if Sortable action activated
     *
     * @return Response
     */
    protected function listAction()
    {
        if (isset($this->entity['list']['actions']['sortable'])) {
            $this->entity['templates']['list'] = 'KRGEasyAdminExtensionBundle:default:list.html.twig';
        }

        return parent::listAction();
    }

    /**
     * Handle ajax Sortable action
     *
     * @return Response
     */
    protected function sortableAction()
    {
        $id = $this->request->query->get('id');
        $relativePosition = $this->request->query->get('relativePosition');
        $field = $this->entity['list']['actions']['sortable']['field'];
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
                'id'    => $id
            ]);

        // Move other items
        foreach ($qb->getQuery()->getResult() as $entry) {
            $entry->setPosition($entry->$getter() + (($entry->$getter() < $newPosition || ($entry->$getter() === $newPosition && $newPosition > $currentPosition)) ? -1 : 1));
        }
        $entity->$setter($newPosition);
        $this->em->flush();

        return new Response();
    }
}