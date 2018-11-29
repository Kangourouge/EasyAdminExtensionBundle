<?php

namespace KRG\EasyAdminExtensionBundle\Model;

use KRG\CoreBundle\Model\ModelInterface;
use KRG\CoreBundle\Model\ModelView;
use KRG\EasyAdminExtensionBundle\IO\IterableResultDecorator;
use KRG\EasyAdminExtensionBundle\IO\IterableResultDecoratorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExportModel implements ModelInterface
{
    public function build(ModelView $view, array $options)
    {
        $data = [
            'sheets' => [],
            'setting' => $options['setting']
        ];
        foreach ($options['sheets'] as &$sheet) {
            $decorator = $sheet['decorator'] ?? IterableResultDecorator::class;

            if (!in_array(IterableResultDecoratorInterface::class, class_implements($decorator))) {
                throw new \InvalidArgumentException(sprintf('Decorator must by type of %s', IterableResultDecoratorInterface::class));
            }
            $data['sheets'][] = [
                'title' => $sheet['title'],
                'fields' => $sheet['fields'],
                'rows' => new $decorator($options['iterator'], $sheet['fields'])
            ];
        }
        $view->setData($data);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['iterator', 'sheets', 'setting']);
        $resolver->setDefault('setting', []);
        $resolver->setAllowedTypes('iterator', \Iterator::class);
        $resolver->setAllowedTypes('sheets', 'array');
        $resolver->setAllowedTypes('setting', 'array');
    }
}