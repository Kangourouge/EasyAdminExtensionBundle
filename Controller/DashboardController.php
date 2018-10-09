<?php

namespace KRG\EasyAdminExtensionBundle\Controller;

use KRG\EasyAdminExtensionBundle\Widget\WidgetInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AdminController
{
    /** @var array */
    protected $widgets;

    public function addWidget(WidgetInterface $widget)
    {
        if ($this->isGranted($this, $widget)) {
            $this->widgets[] = $widget;
        }
    }

    /**
     * @Route("/dashboard", name="admin_dashboard")
     */
    public function dashboardAction(Request $request)
    {
        $this->initialize($request);

        return $this->render('KRGEasyAdminExtensionBundle:default:dashboard.html.twig', [
            'config'  => $this->config,
            'widgets' => $this->widgets,
        ]);
    }
}
