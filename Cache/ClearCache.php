<?php

namespace KRG\EasyAdminExtensionBundle\Cache;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;

class ClearCache implements EventSubscriberInterface
{
    const TYPE_ALL      = 0;
    const TYPE_TWIG     = 1;
    const TYPE_INTL     = 2;
    const TYPE_LIIP     = 4;
    const TYPE_ROUTING  = 8;
    const TYPE_KRG_TWIG = 16;
    const TYPE_KRG_INTL = 32;
    const TYPE_KRG_DATA = 64;
    const TYPE_KRG_ALL  = self::TYPE_KRG_INTL | self::TYPE_KRG_TWIG | self::TYPE_KRG_DATA;

    public static $types = [
        self::TYPE_ALL      => 'All',
        self::TYPE_TWIG     => 'Twig',
        self::TYPE_INTL     => 'Intl',
        self::TYPE_LIIP     => 'Liip',
        self::TYPE_ROUTING  => 'Routing',
        self::TYPE_KRG_ALL  => 'KrgAll',
        self::TYPE_KRG_TWIG => 'KrgTwig',
        self::TYPE_KRG_INTL => 'KrgIntl',
        self::TYPE_KRG_DATA => 'KrgData',
    ];

    /** @var RouterInterface */
    private $router;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var string */
    private $cacheDir;

    /** @var string */
    private $webDir;

    /** @var string */
    private $env;

    public function __construct(RouterInterface $router, FlashBagInterface $flashBag, string $cacheDir, string $webDir, string $env)
    {
        $this->router = $router;
        $this->flashBag = $flashBag;
        $this->cacheDir = $cacheDir;
        $this->webDir = $webDir;
        $this->env = $env;
    }

    public function __call($name, $arguments)
    {
        if (substr($name, 0, 6) === 'remove' && ($type = array_search(substr($name, 6), self::$types)) > -1) {
            return $this->remove($type);
        }

        return null;
    }

    public static function getSubscribedEvents()
    {
        $events = [
            'cache:clear' => 'onRemove',
        ];

        foreach (self::$types as $type) {
            $_type = strtolower(preg_replace('/(?<!^)[A-Z]/', ':$0', $type));
            $events['cache:clear:'.$_type] = 'remove'.$type;
        }

        return $events;
    }

    public function onRemove(Event $event)
    {
        $event->stopPropagation();
        $this->remove();
    }

    public function remove($type = self::TYPE_ALL)
    {
        $types = [
            self::TYPE_TWIG     => sprintf('%s/twig', $this->cacheDir),
            self::TYPE_INTL     => sprintf('%s/translations', $this->cacheDir),
            self::TYPE_LIIP     => sprintf('%s/media/cache', $this->webDir),
            self::TYPE_ROUTING  => null,
            self::TYPE_KRG_TWIG => sprintf('%s/krg/twig', $this->cacheDir),
            self::TYPE_KRG_INTL => sprintf('%s/krg/translations', $this->cacheDir),
            self::TYPE_KRG_DATA => sprintf('%s/krg/data', $this->cacheDir),
        ];

        $message = [];

        $filesystem = new Filesystem();
        foreach ($types as $_type => $dir) {
            if ($type === 0 || ($type & $_type)) {
                if ($_type === self::TYPE_ROUTING) {
                    foreach (['matcher_cache_class', 'generator_cache_class'] as $option) {
                        $className = $this->router->getOption($option);
                        $cacheFile = sprintf('%s/%s.php', $this->cacheDir, $className);
                        $filesystem->remove($cacheFile);
                    }
                    $this->router->warmUp($this->cacheDir);
                    $message[] = 'routing';
                } else {
                    $filesystem->remove($dir);

                    $message[] = str_replace($this->cacheDir.'/', '', $dir);
                }
            }
        }


        if ($this->env === 'dev' && $message) {
            $this->flashBag->add('success', 'Cache cleared for: '.implode(', ', $message).'.');
        }
    }
}
