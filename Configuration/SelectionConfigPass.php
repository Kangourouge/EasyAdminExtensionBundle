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
                    $action['label'] = $action['label'] ?? sprintf('action.%s', $action['name']);
                    $action['paramters'] = $action['paramters'] ?? [];
                }
                unset($action);
            }

            if (isset($config['selection']) || isset($config['export'])) {
                $config['list']['fields'] = array_merge(
                    [[
                        'property'  => '#',
                        'label'      => '<div class="checkbox"><label><input type="checkbox" id="selection_check_all"></label></div>',
                        'type'       => 'selection',
                        'css_filter' => 'w-50',
                        'sortable'  => false,
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
