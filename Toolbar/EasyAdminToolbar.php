<?php

namespace KRG\EasyAdminExtensionBundle\Toolbar;

use Symfony\Component\Templating\EngineInterface;

class EasyAdminToolbar implements ToolbarInterface
{
    /** @var EngineInterface */
    protected $templating;

    public function __construct(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    public function render()
    {
        return $this->templating->render('@KRGEasyAdminExtension/toolbar/easyadmin.html.twig');
    }
}
