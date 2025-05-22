<?php

namespace App\EventListener\AreaGroup;

use App\Enums\EntityHistoryTypes;
use App\Events\AreaGroup\AreaGroupCreatedEvent;
use App\Events\AreaGroup\AreaGroupDeletedEvent;
use App\Events\AreaGroup\AreaGroupUpdatedEvent;
use App\Service\EntityHistory\EntityHistoryService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AreaGroupListener implements EventSubscriberInterface
{
    private $em;
    private $entityHistoryService;
    private $tokenStorage;

    /**
     * VehicleListener constructor.
     * @param EntityManager $em
     * @param EntityHistoryService $entityHistoryService
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        EntityManager $em,
        EntityHistoryService $entityHistoryService,
        TokenStorageInterface $tokenStorage
    ) {
        $this->em = $em;
        $this->entityHistoryService = $entityHistoryService;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            AreaGroupCreatedEvent::NAME => 'onAreaGroupCreated',
            AreaGroupUpdatedEvent::NAME => 'onAreaGroupUpdated',
            AreaGroupDeletedEvent::NAME => 'onAreaGroupDeleted',
        ];
    }

    /**
     * @param AreaGroupUpdatedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onAreaGroupUpdated(AreaGroupUpdatedEvent $event)
    {
        $areaGroup = $event->getAreaGroup();
        $this->entityHistoryService->create(
            $areaGroup,
            Carbon::now('UTC')->getTimestamp(),
            EntityHistoryTypes::AREA_GROUP_UPDATED,
            $event->getUser()
        );

        $this->em->flush();
    }

    /**
     * @param AreaGroupCreatedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onAreaGroupCreated(AreaGroupCreatedEvent $event)
    {
        $areaGroup = $event->getAreaGroup();
        $this->entityHistoryService->create(
            $areaGroup,
            Carbon::now('UTC')->getTimestamp(),
            EntityHistoryTypes::AREA_GROUP_CREATED,
            $event->getUser()
        );

        $this->em->flush();
    }

    /**
     * @param AreaGroupDeletedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onAreaGroupDeleted(AreaGroupDeletedEvent $event)
    {
        $areaGroup = $event->getAreaGroup();
        $this->entityHistoryService->create(
            $areaGroup,
            Carbon::now('UTC')->getTimestamp(),
            EntityHistoryTypes::AREA_GROUP_DELETED,
            $event->getUser()
        );

        $this->em->flush();
    }
}