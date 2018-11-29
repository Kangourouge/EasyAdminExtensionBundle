<?php

namespace KRG\EasyAdminExtensionBundle\IO;

use Doctrine\ORM\Internal\Hydration\IterableResult;
use KRG\EasyAdminExtensionBundle\Model\ExportModel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

interface ExportInterface
{
    /**
     * @param array $sheets
     *
     * @return BinaryFileResponse
     */
    public function render($filename, array $data, array $options = []);
}