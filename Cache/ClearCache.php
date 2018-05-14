<?php

namespace KRG\EasyAdminExtensionBundle\Cache;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;

class ClearCache
{
    const TYPE_ALL = 0;
    const TYPE_TWIG = 1;
    const TYPE_INTL = 2;
    const TYPE_LIIP = 4;
    const TYPE_ROUTING = 8;
    const TYPE_KRG_ALL = 16|32|64;
    const TYPE_KRG_TWIG = 16;
    const TYPE_KRG_INTL = 32;
    const TYPE_KRG_SEO = 64;

    /** @var RouterInterface */
    private $router;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var string */
    private $cacheDir;

    /** @var string */
    private $webDir;

    public function __construct(RouterInterface $router, FlashBagInterface $flashBag, string $cacheDir, string $webDir)
    {
        $this->router = $router;
        $this->flashBag = $flashBag;
        $this->cacheDir = $cacheDir;
        $this->webDir = $webDir;
    }

    public function warmup($type) {
        $types = [
            self::TYPE_TWIG => sprintf('%s/twig', $this->cacheDir),
            self::TYPE_INTL => sprintf('%s/translations', $this->cacheDir),
            self::TYPE_LIIP => sprintf('%s/media/cache', $this->webDir),
            self::TYPE_ROUTING => null,
            self::TYPE_KRG_TWIG => sprintf('%s/krg/twig', $this->cacheDir),
            self::TYPE_KRG_INTL => sprintf('%s/krg/translations', $this->cacheDir),
            self::TYPE_KRG_SEO => sprintf('%s/krg/seo', $this->cacheDir),
        ];

        $filesystem = new Filesystem();

        foreach ($types as $_type => $dir) {
            if ($type === 0 || ($type & $_type)) {
                if ($_type === self::TYPE_ROUTING) {
                    foreach (['matcher_cache_class', 'generator_cache_class'] as $option) {
                        $className = $this->router->getOption($option);
                        $cacheFile = sprintf('%s/%s.php', $this->cacheDir, $className);
                        $filesystem->remove($cacheFile);
                    }
                    $this->flashBag->add('success', 'Remove routing cache');
                }
                else {
                    $filesystem->remove($dir);
                    $this->flashBag->add('success', sprintf('Remove cache %s', $dir));
                }
            }
        }
    }
}
