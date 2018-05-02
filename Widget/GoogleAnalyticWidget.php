<?php

namespace KRG\EasyAdminExtensionBundle\Widget;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

class GoogleAnalyticWidget implements WidgetInterface
{
    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var string
     */
    protected $googleAnalyticsId;

    /**
     * GoogleAnalytics constructor.
     *
     * @param EngineInterface $templating
     */
    public function __construct(EngineInterface $templating, $googleAnalyticsId)
    {
        $this->templating = $templating;
        $this->googleAnalyticsId = $googleAnalyticsId;
    }

    public function render()
    {
        return $this->templating->render('KRGEasyAdminExtensionBundle:widget:google_analytics.html.twig', [
            'clientId' => $this->googleAnalyticsId
        ]);
    }
}