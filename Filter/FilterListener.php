<?php

namespace KRG\EasyAdminExtensionBundle\Filter;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use KRG\EasyAdminExtensionBundle\Form\Type\EasyAdminFilterType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
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
            /** @var FormInterface $form */
            $this->form = $this->formFactory->createNamed(
                $entityConfig['filter']['form_name'],
                EasyAdminFilterType::class,
                null,
                [ 'config' => $entityConfig, 'method' => 'GET' ]
            );
            $this->form->handleRequest($this->request);
        }
    }

    public function onPostQueryBuilder(GenericEvent $event) {
        /** @var array $entityConfig */
        $entityConfig = $event->getArgument('entity');

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $event->getArgument('query_builder');

        if ($this->form instanceof FormInterface) {
            try {
                $fields = $entityConfig['filter']['fields'];
                if ($this->form->isValid() && $this->form->isSubmitted()) {
                    $data = $this->form->getData();
                    if (!empty($data)) {
                        foreach ($fields as $idx => $field) {
                            if (isset($data[sprintf('f%d', $idx)])) {
                                $_data = $data[sprintf('f%d', $idx)];

                                if ($_data instanceof Collection) {
                                    $_data = $_data->toArray();
                                }

                                if (!empty($_data) && isset($field['dql_callback']) && is_callable($field['dql_callback'])) {
                                    call_user_func($field['dql_callback'], $queryBuilder, $field, $_data);
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $exception) {}
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