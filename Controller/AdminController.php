<?php

namespace KRG\EasyAdminExtensionBundle\Controller;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use EasyCorp\Bundle\EasyAdminBundle\Exception\EntityRemoveException;
use KRG\CoreBundle\Export\ExcelExport;
use KRG\CoreBundle\Export\Export;
use KRG\CoreBundle\Export\ExportInterface;
use KRG\CoreBundle\Form\Type\ImportType;
use KRG\CoreBundle\Model\ExportModel;
use KRG\CoreBundle\Model\ModelFactory;
use KRG\EasyAdminExtensionBundle\Filter\FilterListener;
use KRG\EasyAdminExtensionBundle\Form\Type\SelectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
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
                'clone_origin_id' => $entity->getId()
            ]
        );
    }

    protected function importAction()
    {
        $uniqid = $this->request->get('import_file', uniqid(sprintf('krg_import_csv_%s_', $this->entity['name'])));

        $form = $this->createForm(ImportType::class, $uniqid, [
                'class'      => $this->entity['class'],
                'entry_type' => $this->entity['import']['entry_type'],
                'normalizer' => $this->get($this->entity['import']['normalizer'])
            ])
            ->add('submit', SubmitType::class);

        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $data = $form->getData();

            if (count($data['entities']) > 0 && $data['confirm']) {
                $entityManager = $this->getDoctrine()->getManager();
                foreach ($data['entities'] as $entity) {
                    $entityManager->persist($entity);
                }
                $entityManager->flush();

                return $this->redirectToRoute(
                    'easyadmin', [
                        'action' => 'list',
                        'entity' => $this->request->query->get('entity'),
                    ]
                );
            }

            return $this->redirectToRoute('easyadmin', [
                'action' => 'import',
                'entity' => $this->request->query->get('entity'),
                'import_file' => $uniqid
            ]);
        }

        $parameters = [
            'form' => $form->createView()
        ];

        return $this->executeDynamicMethod('render<EntityName>Template', ['import', '@KRGEasyAdminExtension/default/import.html.twig', $parameters]);
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
            $hasSelection = isset($this->entity['selection']) || isset($this->entity['export']);
            if ($parameters['paginator']->getNbResults() > 0 && $hasSelection) {

                $selectionForm = $this->formFactory->create(
                    SelectionType::class, null, [
                    'entities'        => $parameters['paginator']->getIterator(),
                    'execute_actions' => $this->entity['selection']['actions'] ?? [],
                    'export_formats'  => $this->entity['export']['formats'] ?? [],
                    'export_sheets'   => $this->entity['export']['sheets'] ?? [],
                ]);

                $parameters['selection_form'] = $selectionForm->createView();
                $parameters['fields']['id']['form'] = $parameters['selection_form'];

                $selectionForm->handleRequest($this->request);
                if ($selectionForm->isValid() && $selectionForm->isSubmitted()) {
                    $data = $selectionForm->getData();

                    try {
                        foreach (['execute', 'export'] as $button) {
                            if ($selectionForm->has($button) && $selectionForm->get($button)->isClicked()) {
                                switch ($button) {
                                    case 'execute':
                                        $actions = array_column($this->entity['selection']['actions'], null, 'name');
                                        if (count($data['entities']) > 0 && $data['action'] !== null && isset($actions[$data['action']])) {
                                            $action = $actions[$data['action']];

                                            return call_user_func_array([$this, $action['method']], [$data['entities']]);
                                        }
                                        break;

                                    case 'export':

                                        $formats = array_column($this->entity['export']['formats'], null, 'name');

                                        if ($data['format'] !== null && isset($formats[$data['format']])) {
                                            $query = $parameters['paginator']->getAdapter()->getQuery();
                                            if (count($data['entities']) > 0) {
                                                $query = $this->createListQueryBuilder($this->entity['class'], 'ASC', null, 'entity.id in (:selection)')
                                                    ->setParameter('selection', $data['entities'])
                                                    ->getQuery();
                                            }

                                            $query->setMaxResults(null);
                                            $query->setFirstResult(null);

                                            $sheets = $this->entity['export']['sheets'];
                                            if ($data['sheet']) {
                                                $sheets = [$this->entity['export']['sheets'][$data['sheet']]];
                                            }
                                            $model = $this->get(ModelFactory::class)
                                                ->create(
                                                    ExportModel::class, [
                                                    'iterator' => $query->iterate(),
                                                    'sheets'   => $sheets,
                                                ]);

                                            $format = $formats[$data['format']];

                                            /** @var ExportInterface $exporter */
                                            $export = $this->get($format['service']);

                                            $name = preg_replace(
                                                ['/%name%/', '/%date%/', '/%time%/', '/%extension%/'],
                                                [$this->entity['name'], date('Y-m-d'), date('H\hi'), $format['extension']],
                                                $format['filename']
                                            );

                                            $filename = sprintf('%s/%s', sys_get_temp_dir(), $name);

                                            return $export->render($filename, $model, $format['options']);
                                        }
                                }
                            }
                        }
                    } catch (\Exception $exception) {
                        /** @todo add flash message */
                        $this->addFlash('error', $exception->getMessage());
                    }
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
