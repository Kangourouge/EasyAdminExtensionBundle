<?php

namespace KRG\EasyAdminExtensionBundle\Configuration;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;

class SelectionConfigPass implements ConfigPassInterface
{
    public function process(array $backendConfig)
    {
        foreach ($backendConfig['entities'] as $entity => &$config) {

            if (isset($config['selection'])) {
                $config['selection']['actions'] = $config['selection']['actions'] ?? [];

                foreach($config['selection']['actions'] as &$action) {
                    if (is_string($action)) {
                        $action = ['name' => $action];
                    }
                    $action['method'] = $action['method'] ?? sprintf('%sSelectionAction', $action['name']);
                    $action['label'] = $action['label'] ?? sprintf('action.%s', $action['name']);
                }
                unset($action);
            }

            if (isset($config['selection']) || isset($config['export'])) {
                $config['list']['fields'] = array_merge(
                    ['id' => [
                        'property'   => 'id',
                        'label'      => '',
                        'type'       => 'selection',
                        'css_filter' => 'w-50',
                        'template'   => '@KRGEasyAdminExtension/default/field_form.html.twig',
                    ]],
                    $config['list']['fields']
                );
            }
        }
        unset($config);

        return $backendConfig;
    }
}
