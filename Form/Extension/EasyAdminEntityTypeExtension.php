<?php

namespace KRG\EasyAdminExtensionBundle\Form\Extension;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EasyAdminEntityTypeExtension extends AbstractTypeExtension
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('query_builder_method', null);
        $resolver->setNormalizer('query_builder', function (OptionsResolver $resolver) {
            $queryBuilderMethod = $resolver->offsetGet('query_builder_method');

            if (is_string($queryBuilderMethod)) {
                $repository = $this->entityManager->getRepository($resolver->offsetGet('class'));

                if (method_exists($repository, $queryBuilderMethod)) {
                    return call_user_func([$repository, $queryBuilderMethod]);
                }
            }

            return null;
        });
    }

    public function getExtendedType()
    {
        return EntityType::class;
    }
}
