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
                            'choices' => array_flip(ClearCache::$types),
                            'choice_translation_domain' => 'KRGEasyAdminExtensionBundle',
                            'expanded' => true,
                            'multiple' => true
                        ])
                        ->add('Clear', SubmitType::class, [
                            'attr' => ['class' => 'btn btn-danger'],
                            'translation_domain' => 'KRGEasyAdminExtensionBundle'
                        ])
                        ->getForm();


        $form->handleRequest($request);
        if ($form->isValid() && $form->isSubmitted()) {
            $types = $form->get('type')->getData();
            foreach ($types as $type) {
                $this->clearCache->remove((int) $type);
            }
        }

        return $this->render('KRGEasyAdminExtensionBundle:cache:clear.html.twig', [
            'form' => $form->createView()
        ]);
    }
}