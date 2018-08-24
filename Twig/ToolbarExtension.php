<?php

namespace KRG\EasyAdminExtensionBundle\Twig;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use KRG\EasyAdminExtensionBundle\Toolbar\ToolbarInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

class ToolbarExtension extends \Twig_Extension
{
    /** @var array */
    private $toolbar;

    /** @var $entityManager EntityManager */
    private $entityManager;

    /** @var Request */
    private $request;

    /** @var TokenStorage */
    private $tokenStorage;

    /** @var AuthorizationChecker */
    private $authorizationChecker;

    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorizationChecker, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->request = $requestStack->getMasterRequest();
        $this->toolbar = [];
    }

    public function getToolbar(\Twig_Environment $environment)
    {
        try {
            if (!$this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')
                && !$this->authorizationChecker->isGranted('ROLE_ADMIN')
                && !$this->authorizationChecker->isGranted('ROLE_PREVIOUS_ADMIN')) {
                return null;
            }
        } catch (AuthenticationCredentialsNotFoundException $exception) {
            return null;
        }

        if ($this->request === null) {
            return null;
        }

        $blocks = [];
        /** @var $block ToolbarInterface */
        foreach ($this->toolbar as $block) {
            $blocks[] = $block->render();
        }

        return $environment->render('@KRGEasyAdminExtension/toolbar/toolbar.html.twig', [
            'blocks' => $blocks
        ]);
    }

    public function addToolbar($toolbar)
    {
        $this->toolbar[] = $toolbar;
    }

    public function getFunctions()
    {
        return [
            'krg_admin_toolbar' => new \Twig_SimpleFunction('krg_admin_toolbar', [$this, 'getToolbar'], [
                'needs_environment' => true,
                'is_safe'           => ['html'],
            ]),
        ];
    }
}
