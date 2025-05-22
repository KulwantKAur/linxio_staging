<?php

namespace App\EventListener\Reminder;

use App\Enums\EntityHistoryTypes;
use App\Events\Reminder\ReminderCreatedEvent;
use App\Events\Reminder\ReminderDeletedEvent;
use App\Events\Reminder\ReminderEsRefreshEvent;
use App\Events\Reminder\ReminderUpdatedEvent;
use App\Service\EntityHistory\EntityHistoryService;
use App\Service\Reminder\ReminderService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ReminderListener implements EventSubscriberInterface
{
    private $listenedEvents = [];

    private $em;
    private $entityHistoryService;
    private $tokenStorage;
    private $reminderService;
    private ObjectPersister $reminderObjectPersister;


    public function __construct(
        EntityManager $em,
        EntityHistoryService $entityHistoryService,
        TokenStorageInterface $tokenStorage,
        ReminderService $reminderService,
        ObjectPersister $reminderObjectPersister
    ) {
        $this->em = $em;
        $this->entityHistoryService = $entityHistoryService;
        $this->tokenStorage = $tokenStorage;
        $this->reminderService = $reminderService;
        $this->reminderObjectPersister = $reminderObjectPersister;
    }

    public static function getSubscribedEvents()
    {
        return [
            ReminderCreatedEvent::NAME => 'onReminderCreated',
            ReminderUpdatedEvent::NAME => 'onReminderUpdated',
            ReminderDeletedEvent::NAME => 'onReminderDeleted',
            ReminderEsRefreshEvent::NAME => 'onReminderEsRefresh',
        ];
    }

    public function onReminderUpdated(ReminderUpdatedEvent $event)
    {
        $reminder = $event->getReminder();
        $this->reminderService->cleanCache($reminder->getTeam());

        $this->entityHistoryService->create(
            $reminder,
            $reminder->getUpdatedAt() ? $reminder->getUpdatedAt()->getTimestamp() : Carbon::now('UTC')->getTimestamp(),
            EntityHistoryTypes::REMINDER_UPDATED,
            $reminder->getUpdatedBy()
        );

        $this->em->flush();
    }

    /**
     * @param ReminderDeletedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onReminderDeleted(ReminderDeletedEvent $event)
    {
        $reminder = $event->getReminder();
        $this->reminderService->cleanCache($reminder->getTeam());

        $this->entityHistoryService->create(
            $reminder,
            time(),
            EntityHistoryTypes::REMINDER_DELETED,
            $reminder->getUpdatedBy()
        );

        $this->em->flush();
    }

    /**
     * @param ReminderCreatedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onReminderCreated(ReminderCreatedEvent $event)
    {
        $reminder = $event->getReminder();
        $this->reminderService->cleanCache($reminder->getTeam());

        $this->entityHistoryService->create(
            $reminder,
            $reminder->getCreatedAt()->getTimestamp(),
            EntityHistoryTypes::REMINDER_CREATED,
            $reminder->getCreatedBy()
        );

        $this->em->flush();
    }

    public function onReminderEsRefresh(ReminderEsRefreshEvent $event)
    {
        $reminders = $event->getVehicle()->getReminders() ?? [];
        foreach ($reminders as $reminder) {
            $this->reminderObjectPersister->replaceOne($reminder);
        }

        $this->em->flush();
    }
}