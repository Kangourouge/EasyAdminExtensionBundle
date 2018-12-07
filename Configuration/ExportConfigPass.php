<?php

namespace KRG\EasyAdminExtensionBundle\Configuration;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;
use KRG\CoreBundle\Export\CsvExport;
use KRG\CoreBundle\Export\Export;
use KRG\CoreBundle\Export\XlsExport;
use KRG\CoreBundle\Model\ImportModel;
use KRG\CoreBundle\Model\ModelFactory;
use KRG\EasyAdminExtensionBundle\Serializer\ImportNormalizer;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ExportConfigPass implements ConfigPassInterface
{
    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var ModelFactory */
    protected $modelFactory;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * ExportConfigPass constructor.
     *
     * @param FormFactoryInterface $formFactory
     * @param ModelFactory $modelFactory
     * @param TranslatorInterface $translator
     */
    public function __construct(FormFactoryInterface $formFactory, ModelFactory $modelFactory, TranslatorInterface $translator)
    {
        $this->formFactory = $formFactory;
        $this->modelFactory = $modelFactory;
        $this->translator = $translator;
    }

    public function process(array $backendConfig)
    {
        foreach ($backendConfig['entities'] as &$config) {
            if (isset($config['export'])) {

                $config['export']['formats'] = $config['export']['formats'] ?? ['csv', 'xls'];

                foreach($config['export']['formats'] as &$format) {
                    if (is_string($format)) {
                        $format = ['name' => $format];
                        $format['label'] = $format['label'] ?? sprintf('export.%s', $format['name']);
                        $format['options'] = $format['options'] ?? [];

                        switch ($format['name']) {
                            case 'csv':
                                $format = array_replace_recursive([
                                    'extension' => 'csv',
                                    'filename' => 'export_%name%_%date%_%time%.csv',
                                    'service' => CsvExport::class
                                ], $format);
                                break;

                            case 'xls':
                                $format = array_replace_recursive([
                                    'extension' => 'xls',
                                    'filename' => 'export_%name%_%date%_%time%.xls',
                                    'service' => XlsExport::class
                                ], $format);
                                break;
                        }
                    }
                }
                unset($format);

                foreach($config['export']['sheets'] as $idx => &$sheet) {
                    $sheet['label'] = $sheet['label'] ?? sprintf('%s export %d', $config['name'], $idx);
                    foreach($sheet['fields'] as &$field) {
                        if (is_string($field)) {
                            $field = ['property_path' => $field];
                            $field['label'] = $field['label'] ?? $field['property_path'];
                            $field['options'] = $field['options'] ?? [];
                        }
                    }
                    unset($field);
                }
                unset($sheet);

                if (isset($config['import'])) {
                    $model = $this->modelFactory->create(ImportModel::class, [
                        'type' => $config['import']['entry_type']
                    ]);

                    $config['export']['sheets'][] = [
                        'label' => 'Export/Import',
                        'fields' => $model['columns']
                    ];
                }
            }

        }
        unset($config);

        return $backendConfig;
    }
}