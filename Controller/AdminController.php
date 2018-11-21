<?php

namespace KRG\EasyAdminExtensionBundle\Controller;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use KRG\EasyAdminExtensionBundle\Filter\FilterListener;
use KRG\EasyAdminExtensionBundle\Form\Type\SelectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends \EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController
{
    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var FilterListener */
    protected $filterListener;

    /**
     * AdminController constructor.
     *
     * @param FormFactoryInterface $formFactory
     * @param FilterListener $filterListener
     */
    public function __construct(FormFactoryInterface $formFactory, FilterListener $filterListener)
    {
        $this->formFactory = $formFactory;
        $this->filterListener = $filterListener;
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

        return $this->redirectToRoute(
            'easyadmin', [
                'action' => 'edit',
                'id'     => $clone->getId(),
                'entity' => $this->request->query->get('entity'),
            ]
        );
    }

    protected function createNewForm($entity, array $entityProperties)
    {
        if (isset($this->entity['new']['form_type'])) {
            return $this->formFactory->createNamed('easyadmin', $this->entity['new']['form_type'], $entity);
        }
        if (isset($this->entity['edit']['form_type'])) {
            return $this->formFactory->createNamed('easyadmin', $this->entity['edit']['form_type'], $entity);
        }

        return parent::createNewForm($entity, $entityProperties); // TODO: Change the autogenerated stub
    }

    protected function createEditForm($entity, array $entityProperties)
    {
        if (isset($this->entity['edit']['form_type'])) {
            return $this->formFactory->createNamed('easyadmin', $this->entity['edit']['form_type'], $entity);
        }

        return parent::createEditForm($entity, $entityProperties);
    }

    protected function renderTemplate($actionName, $templatePath, array $parameters = array())
    {
        if ($actionName === 'list' || $actionName === 'search') {
            if (($filterForm = $this->filterListener->getForm()) instanceof FormInterface) {
                $parameters['form_filter_submitted'] = $filterForm->isValid() && $filterForm->isSubmitted();
                $parameters['form_filter'] = $filterForm->createView();
            }

            $parameters['selection_form'] = null;
            if ($parameters['paginator']->getNbResults() > 0) {
                $actions = array_column($this->entity['selection']['actions'], null, 'name');

                if (count($actions) > 0) {

                    $selectionForm = $this->formFactory->create(
                        SelectionType::class, null, [
                        'actions'      => $actions,
                        'entities'     => $parameters['paginator']->getIterator(),
                        'allow_import' => isset($this->entity['import']),
                        'allow_export' => isset($this->entity['export']),
                    ]
                    );

                    $selectionForm->handleRequest($this->request);
                    if ($selectionForm->isValid() && $selectionForm->isSubmitted()) {
                        $data = $selectionForm->getData();

                        try {
                            foreach (['submit', 'export', 'import'] as $button) {
                                if ($selectionForm->has($button) && $selectionForm->get($button)->isClicked()) {
                                    switch ($button) {
                                        case 'submit':
                                            if (count($data['entities']) > 0 && $data['action'] !== null) {
                                                $action = $actions[$data['action']];

                                                return call_user_func_array([$this, $action['method']], [$data['entities']]);
                                            }
                                            break;

                                        case 'export':
                                            $query = $parameters['paginator']->getAdapter()->getQuery();
                                            if (count($data['entities']) > 0) {
                                                $query = $this->createListQueryBuilder($this->entity['class'], 'ASC', null, 'entity.id in (:selection)')
                                                    ->setParameter('selection', $data['entities'])
                                                    ->getQuery();
                                            }
                                            break;

                                        case 'import':
                                            break;
                                    }
                                }
                            }
                        } catch (\Exception $exception) {
                        }
                    }

                    $parameters['selection_form'] = $selectionForm->createView();
                    $parameters['fields']['id']['form'] = $parameters['selection_form'];
                }
            }
        }

        return parent::renderTemplate($actionName, $templatePath, $parameters);
    }

    public function deleteSelectionAction(array $entities)
    {
        $this->dispatch(EasyAdminEvents::PRE_DELETE);

        foreach ($entities as $entity) {
            $this->dispatch(EasyAdminEvents::PRE_REMOVE, array('entity' => $entity));

            $this->executeDynamicMethod('preRemove<EntityName>Entity', array($entity));

            try {
                $this->executeDynamicMethod('remove<EntityName>Entity', array($entity));
            } catch (ForeignKeyConstraintViolationException $e) {
                throw new EntityRemoveException(array('entity_name' => $this->entity['name'], 'message' => $e->getMessage()));
            }

            $this->dispatch(EasyAdminEvents::POST_REMOVE, array('entity' => $entity));
        }

        $this->dispatch(EasyAdminEvents::POST_DELETE);

        return $this->redirectToReferrer();
    }
}
