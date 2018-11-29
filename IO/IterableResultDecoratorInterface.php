<?php

namespace KRG\EasyAdminExtensionBundle\IO;

interface IterableResultDecoratorInterface
{
    /**
     * @param $item
     *
     * @return array
     */
    public function buildRow($item);
}