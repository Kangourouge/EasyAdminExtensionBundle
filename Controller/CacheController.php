<?php

namespace KRG\EasyAdminExtensionBundle\Controller;

use KRG\EasyAdminExtensionBundle\Cache\ClearCache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class WidgetController
 * @package KRG\EasyAdminExtensionBundle\Controller
 * @Route("/admin/cache")
 */
class CacheController extends Controller
{
    /**
     * @var ClearCache
     */
    private $clearCache;

    /**
     * CacheController constructor.
     *
     * @param ClearCache $clearCache
     */
    public function __construct(ClearCache $clearCache)
    {
        $this->clearCache = $clearCache;
    }

    /**
     * @Route("/clear", name="krg_easyadmin_cache_clear")
     */
    public function clearAction(Request $request) {

        $form = $this->createFormBuilder()
                        ->add('type', ChoiceType::class, [
                            'choices' => [
                                'All' => ClearCache::TYPE_ALL,
                                'Twig' => ClearCache::TYPE_TWIG,
                                'Translations' => ClearCache::TYPE_INTL,
                                'Media' => ClearCache::TYPE_LIIP,
                                'KRG All' => ClearCache::TYPE_KRG_ALL,
                                'KRG Twig' => ClearCache::TYPE_KRG_TWIG,
                                'KRG Translations' => ClearCache::TYPE_KRG_INTL
                            ],
                            'expanded' => true,
                            'multiple' => true
                        ])
                        ->add('action.clear', SubmitType::class)
                        ->getForm();


        $form->handleRequest($request);
        if ($form->isValid() && $form->isSubmitted()) {
            $types = $form->get('type')->getData();
            foreach ($types as $type) {
                $this->clearCache->warmup((int) $type);
            }
        }

        return $this->render('KRGEasyAdminExtensionBundle:cache:clear.html.twig', [
            'form' => $form->createView()
        ]);
    }
}