<?php

namespace KRG\EasyAdminExtensionBundle\EventListener;

use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public static function getSubscribedEvents()
    {
        return [
            EasyAdminEvents::PRE_NEW    => ['isGranted'],
            EasyAdminEvents::PRE_LIST   => ['isGranted'],
            EasyAdminEvents::PRE_SEARCH => ['isGranted'],
            EasyAdminEvents::PRE_SHOW   => ['isGranted'],
            EasyAdminEvents::PRE_EDIT   => ['isGranted'],
            EasyAdminEvents::PRE_DELETE => ['isGranted'],
        ];
    }

    public function isGranted(GenericEvent $event)
    {
        $entity = $event->getSubject();

        $action = $event->getArgument('request')->query->get('action');

        if (!$this->authorizationChecker->isGranted($action, $entity)) {
            throw new AccessDeniedException();
        }
    }
}
