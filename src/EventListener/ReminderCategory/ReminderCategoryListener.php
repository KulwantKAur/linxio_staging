<?php

namespace App\EventListener\ReminderCategory;

use App\Enums\EntityHistoryTypes;
use App\Events\ReminderCategory\ReminderCategoryCreatedEvent;
use App\Events\ReminderCategory\ReminderCategoryDeletedEvent;
use App\Events\ReminderCategory\ReminderCategoryUpdatedEvent;
use App\Service\EntityHistory\EntityHistoryService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ReminderCategoryListener implements EventSubscriberInterface
{
    private $listenedEvents = [];

    private $em;
    private $entityHistoryService;
    private $tokenStorage;

    /**
     * ReminderListener constructor.
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
            ReminderCategoryCreatedEvent::NAME => 'onReminderCreated',
            ReminderCategoryUpdatedEvent::NAME => 'onReminderUpdated',
            ReminderCategoryDeletedEvent::NAME => 'onReminderDeleted',
        ];
    }

    /**
     * @param ReminderCategoryUpdatedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onReminderUpdated(ReminderCategoryUpdatedEvent $event)
    {
        $reminderCategory = $event->getReminderCategory();
        $this->entityHistoryService->create(
            $reminderCategory,
            $reminderCategory->getUpdatedAt() ? $reminderCategory->getUpdatedAt()->getTimestamp() : Carbon::now('UTC')->getTimestamp(),
            EntityHistoryTypes::REMINDER_CATEGORY_UPDATED,
            $reminderCategory->getUpdatedBy()
        );

        $this->em->flush();
    }

    /**
     * @param ReminderCategoryDeletedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onReminderDeleted(ReminderCategoryDeletedEvent $event)
    {
        $reminderCategory = $event->getReminderCategory();
        $this->entityHistoryService->create(
            $reminderCategory,
            time(),
            EntityHistoryTypes::REMINDER_CATEGORY_DELETED,
            $reminderCategory->getUpdatedBy()
        );

        $this->em->flush();
    }

    /**
     * @param ReminderCategoryCreatedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onReminderCreated(ReminderCategoryCreatedEvent $event)
    {
        $reminderCategory = $event->getReminderCategory();
        $this->entityHistoryService->create(
            $reminderCategory,
            $reminderCategory->getCreatedAt()->getTimestamp(),
            EntityHistoryTypes::REMINDER_CATEGORY_CREATED,
            $reminderCategory->getCreatedBy()
        );

        $this->em->flush();
    }
}