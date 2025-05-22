<?php

namespace App\EventListener\ServiceRecord;

use App\Enums\EntityHistoryTypes;
use App\Events\Repair\RepairCreatedEvent;
use App\Events\Repair\RepairDeletedEvent;
use App\Events\Repair\RepairUpdatedEvent;
use App\Events\ServiceRecord\ServiceRecordCreatedEvent;
use App\Events\ServiceRecord\ServiceRecordDeletedEvent;
use App\Events\ServiceRecord\ServiceRecordUpdatedEvent;
use App\Service\EntityHistory\EntityHistoryService;
use App\Service\Reminder\ReminderService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ServiceRecordListener implements EventSubscriberInterface
{
    private $em;
    private $entityHistoryService;
    private $tokenStorage;
    private $reminderService;

    /**
     * ServiceRecordListener constructor.
     * @param EntityManager $em
     * @param EntityHistoryService $entityHistoryService
     * @param TokenStorageInterface $tokenStorage
     * @param ReminderService $reminderService
     */
    public function __construct(
        EntityManager $em,
        EntityHistoryService $entityHistoryService,
        TokenStorageInterface $tokenStorage,
        ReminderService $reminderService
    ) {
        $this->em = $em;
        $this->entityHistoryService = $entityHistoryService;
        $this->tokenStorage = $tokenStorage;
        $this->reminderService = $reminderService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ServiceRecordCreatedEvent::NAME => 'onServiceRecordCreated',
            ServiceRecordUpdatedEvent::NAME => 'onServiceRecordUpdated',
            ServiceRecordDeletedEvent::NAME => 'onServiceRecordDeleted',
            RepairCreatedEvent::NAME => 'onRepairCreated',
            RepairUpdatedEvent::NAME => 'onRepairUpdated',
            RepairDeletedEvent::NAME => 'onRepairDeleted',
        ];
    }

    /**
     * @param ServiceRecordUpdatedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onServiceRecordUpdated(ServiceRecordUpdatedEvent $event)
    {
        $serviceRecord = $event->getServiceRecord();
        $this->reminderService->cleanCache($serviceRecord->getTeam());

        $this->entityHistoryService->create(
            $serviceRecord,
            $serviceRecord->getUpdatedAt()
                ? $serviceRecord->getUpdatedAt()->getTimestamp()
                : Carbon::now('UTC')->getTimestamp(),
            EntityHistoryTypes::SERVICE_RECORD_UPDATED,
            $serviceRecord->getUpdatedBy()
        );

        $this->em->flush();
    }

    /**
     * @param ServiceRecordDeletedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onServiceRecordDeleted(ServiceRecordDeletedEvent $event)
    {
        $serviceRecord = $event->getServiceRecord();
        $this->reminderService->cleanCache($serviceRecord->getTeam());

        $this->entityHistoryService->create(
            $serviceRecord,
            time(),
            EntityHistoryTypes::SERVICE_RECORD_DELETED,
            $serviceRecord->getUpdatedBy()
        );

        $this->em->flush();
    }

    /**
     * @param ServiceRecordCreatedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onServiceRecordCreated(ServiceRecordCreatedEvent $event)
    {
        $serviceRecord = $event->getServiceRecord();
        $this->reminderService->cleanCache($serviceRecord->getTeam());

        $this->entityHistoryService->create(
            $serviceRecord,
            $serviceRecord->getCreatedAt()->getTimestamp(),
            EntityHistoryTypes::SERVICE_RECORD_CREATED,
            $serviceRecord->getCreatedBy()
        );

        $this->em->flush();
    }

    /**
     * @param RepairUpdatedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onRepairUpdated(RepairUpdatedEvent $event)
    {
        $serviceRecord = $event->getRepair();
        $this->reminderService->cleanCache($serviceRecord->getTeam());

        $this->entityHistoryService->create(
            $serviceRecord,
            $serviceRecord->getUpdatedAt()
                ? $serviceRecord->getUpdatedAt()->getTimestamp()
                : Carbon::now('UTC')->getTimestamp(),
            EntityHistoryTypes::REPAIR_UPDATED,
            $serviceRecord->getUpdatedBy()
        );

        $this->em->flush();
    }

    /**
     * @param RepairDeletedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onRepairDeleted(RepairDeletedEvent $event)
    {
        $serviceRecord = $event->getRepair();
        $this->reminderService->cleanCache($serviceRecord->getTeam());

        $this->entityHistoryService->create(
            $serviceRecord,
            time(),
            EntityHistoryTypes::REPAIR_DELETED,
            $serviceRecord->getUpdatedBy()
        );

        $this->em->flush();
    }

    /**
     * @param RepairCreatedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onRepairCreated(RepairCreatedEvent $event)
    {
        $serviceRecord = $event->getRepair();
        $this->reminderService->cleanCache($serviceRecord->getTeam());

        $this->entityHistoryService->create(
            $serviceRecord,
            $serviceRecord->getCreatedAt()->getTimestamp(),
            EntityHistoryTypes::REPAIR_CREATED,
            $serviceRecord->getCreatedBy()
        );

        $this->em->flush();
    }
}