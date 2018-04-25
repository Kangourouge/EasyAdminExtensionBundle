<?php

namespace KRG\EasyAdminExtensionBundle\Widget;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Gedmo\Loggable\Entity\LogEntry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

class LoggableWidget implements WidgetInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * LoggableWidget constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param EngineInterface $templating
     */
    public function __construct(EntityManagerInterface $entityManager, EngineInterface $templating)
    {
        $this->entityManager = $entityManager;
        $this->templating = $templating;
    }

    public function render()
    {
        /** @var EntityRepository $repository */
        $repository = $this->entityManager->getRepository(LogEntry::class);
        $data = $repository
                        ->createQueryBuilder('l')
                        ->select('l.username', 'l.action', 'l.loggedAt', 'l.objectClass')
                        ->setMaxResults(50)
                        ->orderBy('l.loggedAt', 'DESC')
                        ->getQuery()
                            ->getScalarResult();

        return $this->templating->render('KRGEasyAdminExtensionBundle:widget:loggable.html.twig', [
            'data' => $data
        ]);
    }
}