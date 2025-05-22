<?php

namespace App\EventListener\Team;

use App\Entity\Team;
use App\Service\Notification\NotificationService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;

class TeamEntityListener
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager, private readonly NotificationService $notificationService)
    {
        $this->entityManager = $entityManager;
    }

    public function prePersist(Team $team, PrePersistEventArgs $args)
    {
        $team->setEntityManager($this->entityManager);

        return $team;
    }

    public function postLoad(Team $team, PostLoadEventArgs $args)
    {
        $team->setEntityManager($this->entityManager);
    }

    public function postPersist(Team $team)
    {
        if ($team->isChevron()) {
            $this->notificationService->initTeamDefaultNotification($team);
        }
    }
}