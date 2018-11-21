<?php

namespace KRG\EasyAdminExtensionBundle\Configuration;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;
use KRG\DoctrineExtensionBundle\Entity\Sortable\SortableInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

class SelectionConfigPass implements ConfigPassInterface
{
    public function process(array $backendConfig)
    {
        foreach ($backendConfig['entities'] as $entity => &$config) {

            $config['selection']['actions'] = $config['selection']['actions'] ?? [];

            foreach($config['selection']['actions'] as &$action) {
                if (is_string($action)) {
                    $action = ['name' => $action];
                }
                $action['method'] = $action['method'] ?? sprintf('%sSelectionAction', $action['name']);
                $action['label'] = $action['label'] ?? $action['name'];
            }
            unset($action);

            if (count($config['selection']['actions']) > 0) {
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
