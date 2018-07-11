<?php

namespace KRG\EasyAdminExtensionBundle\Widget;

use KRG\CoreBundle\Annotation\IsGranted;
use KRG\UserBundle\Entity\UserInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * @IsGranted(UserInterface::ROLE_SUPER_ADMIN)
 */
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
        if($this->googleAnalyticsId === null || strlen($this->googleAnalyticsId) === 0) {
            return null ;
        }
        return $this->templating->render('KRGEasyAdminExtensionBundle:widget:google_analytics.html.twig', [
            'clientId' => $this->googleAnalyticsId
        ]);
    }
}