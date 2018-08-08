<?php

namespace KRG\EasyAdminExtensionBundle\Controller;

use KRG\EasyAdminExtensionBundle\Cache\ClearCache;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/cache")
 */
class CacheController extends Controller
{
    /** @var ClearCache */
    private $clearCache;

    public function __construct(ClearCache $clearCache)
    {
        $this->clearCache = $clearCache;
    }

    /**
     * @Route("/clear", name="krg_easyadmin_cache_clear")
     */
    public function clearAction(Request $request)
    {
        $form = $this
             ->createFormBuilder()
             ->add('type', ChoiceType::class, [
                'choices'                   => array_flip(ClearCache::$types),
                'choice_translation_domain' => 'KRGEasyAdminExtensionBundle',
                'expanded'                  => true,
                'multiple'                  => true,
                'label'                     => false,
             ])
             ->getForm();

        $form->handleRequest($request);
        if ($form->isValid() && $form->isSubmitted()) {
            $types = $form->get('type')->getData();
            foreach ($types as $type) {
                $this->clearCache->remove((int)$type);
            }
        }

        return $this->render('KRGEasyAdminExtensionBundle:cache:clear.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
