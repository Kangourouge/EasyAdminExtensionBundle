<?php

namespace KRG\EasyAdminExtensionBundle\Widget;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Gedmo\Loggable\Entity\LogEntry;
use KRG\CoreBundle\Annotation\IsGranted;
use KRG\UserBundle\Entity\UserInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * @IsGranted(UserInterface::ROLE_SUPER_ADMIN)
 */
class LoggableWidget implements WidgetInterface
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var EngineInterface */
    protected $templating;

    public function __construct(EntityManagerInterface $entityManager, EngineInterface $templating)
    {
        $this->entityManager = $entityManager;
        $this->templating = $templating;
    }

    public function render()
    {
        $maxResults = 20;
        $data = $this->entityManager->getRepository(LogEntry::class)
                                    ->createQueryBuilder('l')
                                    ->select('l.username', 'l.action', 'l.loggedAt', 'l.objectClass')
                                    ->setMaxResults($maxResults)
                                    ->orderBy('l.loggedAt', 'DESC')
                                    ->getQuery()
                                    ->getScalarResult();

        return $this->templating->render('KRGEasyAdminExtensionBundle:widget:loggable.html.twig', [
            'data'       => $data,
            'maxResults' => $maxResults,
        ]);
    }
}
