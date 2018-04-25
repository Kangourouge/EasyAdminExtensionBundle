<?php

namespace KRG\EasyAdminExtensionBundle\Widget;

use Symfony\Component\HttpFoundation\Response;

interface WidgetInterface
{
    /**
     * @return string
     */
    public function render();
}