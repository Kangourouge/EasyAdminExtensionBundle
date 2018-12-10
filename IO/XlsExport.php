<?php

namespace KRG\EasyAdminExtensionBundle\IO;

use Doctrine\ORM\Internal\Hydration\IterableResult;
use KRG\EasyAdminExtensionBundle\Model\ExportModel;
use PhpOffice\PhpSpreadsheet\Settings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use SimpleExcel\SimpleExcel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class XlsExport
 * @package KRG\EasyAdminExtensionBundle\IO
 * @see https://docs.microsoft.com/en-us/previous-versions/office/developer/office-xp/aa140066(v=office.10)
 * @see https://phpspreadsheet.readthedocs.io/en/develop/topics/file-formats/#xml
 */
class XlsExport implements ExportInterface
{
    /** @var EngineInterface */
    protected $templating;

    /** @var string */
    protected $webDir;

    /**
     * XlsExport constructor.
     *
     * @param EngineInterface $templating
     * @param string $webDir
     */
    public function __construct(EngineInterface $templating, string $webDir)
    {
        $this->templating = $templating;
        $this->webDir = $webDir;
    }

    public function render($filename, array $data, array $options = [])
    {
        $defaultSetting = [
            'Author' => 'Kangourouge',
            'Company' => 'Kangourouge',
            'Colors' => ['#000000', '#FFFFFF', '#C1C1C1'],
            'WindowHeight' => 16080,
            'WindowWidth' => 25600,
            'WindowTopX' => 29976,
            'WindowTopY' => 1920,
            'ProtectStructure' => 'False',
            'ProtectWindows' => 'False',
            'DisplayInkNotes' => 'False',
            'FontName' => 'Arial',
            'Family' => 'Arial',
            'Color' => '#000000',
            'THeadColor' => '#FFFFFF',
            'THeadBackgroundColor' => '#757575',
            'TBodyColor' => '#000000',
            'TBodyBackgroundColor' => '#FFFFFF',
            'Logo' => '/frontend/images/logo.png',
            'ImageHeight' => '150',
            'RowHeight' => '50',
        ];

        $data['setting'] = array_replace_recursive($defaultSetting, $data['setting'], $options);

        $xml = $this->templating->render('@KRGEasyAdminExtension/export/layout.xml.twig', $data);

        file_put_contents(sprintf('%s.xml', $filename), $xml);


        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xml();
        $spreadsheet = $reader->load(sprintf('%s.xml', $filename));
        foreach($spreadsheet->getWorksheetIterator() as $worksheet) {
            foreach($worksheet->getComments() as $comment) {
                $text = $comment->getText()->getPlainText();
                if (preg_match('/^krg:image;([0-9]+);([0-9]+);(.+);$/', $text, $match)) {
                    $cell = $worksheet->getCellByColumnAndRow((int) $match[1], (int) $match[2], false);
                    if ($cell !== null) {
                        $drawing = new Drawing();
                        $drawing->setPath($this->webDir . $match[3]);
                        $drawing->setResizeProportional(1);
                        $drawing->setWorksheet($worksheet);
                        $drawing->setCoordinates($cell->getCoordinate());
                    }
                }
            }
        }

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        $writer->save(sprintf('%s.xlsx', $filename));

        return new BinaryFileResponse(sprintf('%s.xlsx', $filename), 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => sprintf('attachment; filename="%s.xls"', $filename)
        ]);
    }
}