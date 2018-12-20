<?php

namespace KRG\EasyAdminExtensionBundle\Widget;

use Doctrine\ORM\EntityManagerInterface;
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
        $data = $this->entityManager
            ->getRepository(LogEntry::class)
            ->createQueryBuilder('log_entry')
            ->select('log_entry.username', 'log_entry.action', 'log_entry.loggedAt', 'log_entry.objectClass', 'log_entry.objectId')
            ->setMaxResults($maxResults)
            ->orderBy('log_entry.loggedAt', 'DESC')
            ->getQuery()
            ->getScalarResult();

        $dataRemoved = [];
        foreach ($data as $entry) {
            if ($entry['action'] === 'remove') {
                $dataRemoved[$entry['objectClass']][] = $entry['objectId'];
            }
        }

        return $this->templating->render('KRGEasyAdminExtensionBundle:widget:loggable.html.twig', [
            'data'        => $data,
            'dataRemoved' => $dataRemoved,
            'maxResults'  => $maxResults,
        ]);
    }
}
