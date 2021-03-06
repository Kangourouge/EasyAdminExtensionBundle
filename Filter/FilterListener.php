<?php

namespace KRG\EasyAdminExtensionBundle\Filter;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Gedmo\SoftDeleteable\SoftDeleteable;
use KRG\EasyAdminExtensionBundle\Form\Type\FilterType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class FilterListener implements EventSubscriberInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var FormInterface */
    private $form;

    /**
     * FilterListener constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(EntityManagerInterface $entityManager, FormFactoryInterface $formFactory)
    {
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST                      => 'onRequest',
            EasyAdminEvents::POST_INITIALIZE           => 'onPostInit',
            EasyAdminEvents::POST_LIST_QUERY_BUILDER   => 'onPostQueryBuilder',
            EasyAdminEvents::POST_SEARCH_QUERY_BUILDER => 'onPostQueryBuilder',
        ];
    }

    public function onRequest(GetResponseEvent $event)
    {
        if ($event->getRequest()->get('_route') !== 'easyadmin') {
            return;
        }

        $filters = $this->entityManager->getFilters();
        if ($filters->has('softdeleteable') && $filters->isEnabled('softdeleteable')) {
            $filters->disable('softdeleteable');
        }
    }

    public function onPostInit(GenericEvent $event)
    {
        $entityConfig = $event->getArgument('entity');

        if (isset($entityConfig['filter'])) {
            $options = ['required' => false, 'method' => 'GET'];

            if ($entityConfig['filter']['form_type'] === FilterType::class) {
                $options['config'] = $entityConfig;
            }

            /** @var FormInterface $form */
            $this->form = $this->formFactory->createNamed(
                $entityConfig['filter']['form_name'],
                $entityConfig['filter']['form_type'],
                [],
                $options
            );

            $this->form->add('reset', SubmitType::class);
            $this->form->add('filter', SubmitType::class);

            $this->form->handleRequest($event->getArgument('request'));
        }
    }

    public function onPostQueryBuilder(GenericEvent $event)
    {
        /** @var array $entityConfig */
        $entityConfig = $event->getArgument('entity');

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $event->getArgument('query_builder');

        if (in_array(SoftDeleteable::class, class_implements($entityConfig['class']))) {
            $queryBuilder->andWhere('entity.deletedAt IS NULL');
        }

        if ($this->form instanceof FormInterface) {
            $data = $this->form->getData();
            if ($this->form->isValid() && $this->form->isSubmitted()) {
                $data = $this->form->getData();
            }

            if (!empty($data)) {
                $queryBuilder = call_user_func($entityConfig['filter']['query_builder_callback'], $queryBuilder, $entityConfig, $data);
                $event->setArgument('query_builder', $queryBuilder);
            }
        }
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }
}
