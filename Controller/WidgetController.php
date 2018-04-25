<?php

namespace KRG\EasyAdminExtensionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class WidgetController
 * @package KRG\EasyAdminExtensionBundle\Controller
 * @Route("/admin/widget/")
 */
class WidgetController extends Controller
{
    /**
     * @Route("/ga", name="krg_easyadmin_google_analytics")
     */
    public function gaAction() {
        return new Response('Google analytics');
    }
}