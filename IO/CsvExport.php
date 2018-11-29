<?php

namespace KRG\EasyAdminExtensionBundle\IO;

use Doctrine\ORM\Internal\Hydration\IterableResult;
use KRG\EasyAdminExtensionBundle\Model\ExportModel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Translation\TranslatorInterface;

class CsvExport implements ExportInterface
{
    /** @var TranslatorInterface */
    protected $translator;

    /**
     * CsvExport constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function render($filename, array $data, array $options = [])
    {
        $handle = fopen($filename, 'w');

        $defaultSetting = [
            'setting' => [
                'delimiter' => ',',
                'enclosure' => '"',
                'escape_char' => '\\'
            ]
        ];

        $data = array_replace_recursive($defaultSetting, $data, $options);

        foreach ($data['sheets'] as $sheet) {
            $row = [];
            foreach ($sheet['fields'] as $field) {
                $row[] = $this->translator->trans($field['label']);
            }
            fputcsv($handle, $row, $data['setting']['delimiter'], $data['setting']['enclosure'], $data['setting']['escape_char']);

            foreach ($sheet['rows'] as $row) {
                fputcsv($handle, $row, $data['setting']['delimiter'], $data['setting']['enclosure'], $data['setting']['escape_char']);
            }
        }

        rewind($handle);
        fclose($handle);

        return new BinaryFileResponse($filename, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => sprintf('attachment; filename="%s.csv"', $filename)
        ]);
    }
}