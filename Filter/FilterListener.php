<?php

namespace KRG\EasyAdminExtensionBundle\Filter;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use KRG\EasyAdminExtensionBundle\Form\Type\EasyAdminFilterType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class FilterListener implements EventSubscriberInterface
{
    /** @var null|Request  */
    private $request;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var FormInterface */
    private $form;

    /**
     * FilterListener constructor.
     *
     * @param RequestStack $requestStack
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(RequestStack $requestStack, FormFactoryInterface $formFactory)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->formFactory = $formFactory;
    }

    public static function getSubscribedEvents()
    {
        return [
            EasyAdminEvents::POST_INITIALIZE => 'onPostInit',
            EasyAdminEvents::POST_LIST_QUERY_BUILDER => 'onPostQueryBuilder',
            EasyAdminEvents::POST_SEARCH_QUERY_BUILDER => 'onPostQueryBuilder',
        ];
    }

    public function onPostInit(GenericEvent $event) {

        $entityConfig = $event->getArgument('entity');

        if (isset($entityConfig['filter'])) {
            $options = ['required' => false];

            if ($entityConfig['filter']['form_type'] === EasyAdminFilterType::class) {
                $options['config'] = $entityConfig;
            }

            /** @var FormInterface $form */
            $this->form = $this->formFactory->createNamed(
                $entityConfig['filter']['form_name'],
                $entityConfig['filter']['form_type'],
                null,
                $options
            );

            $this->form->add('filter', SubmitType::class);
            $this->form->add('reset', ResetType::class);

            $this->form->handleRequest($this->request);
        }
    }

    public function onPostQueryBuilder(GenericEvent $event) {

        /** @var array $entityConfig */
        $entityConfig = $event->getArgument('entity');
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $event->getArgument('query_builder');
        if ($this->form instanceof FormInterface) {
            if ($this->form->isValid() && $this->form->isSubmitted()) {
                $data = $this->form->getData();
                if (!empty($data)) {
                    $queryBuilder = call_user_func($entityConfig['filter']['query_builder_callback'], $queryBuilder, $entityConfig, $data);
                    if (!$queryBuilder instanceof QueryBuilder) {
                        throw new \RuntimeException('The query_builder_callback must return a QueryBuilder instance.');
                    }
                    $event->setArgument('query_builder', $queryBuilder);
                }
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