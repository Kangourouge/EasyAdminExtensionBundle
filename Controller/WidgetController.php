<?php

namespace KRG\EasyAdminExtensionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WidgetController extends Controller
{
    /**
     * @Route("/ga", name="krg_easyadmin_google_analytics")
     */
    public function gaAction()
    {
        return new Response('Google analytics');
    }
}
