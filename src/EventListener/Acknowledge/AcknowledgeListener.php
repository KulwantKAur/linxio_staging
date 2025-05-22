<?php

namespace App\EventListener\Acknowledge;

use App\Enums\EntityHistoryTypes;
use App\Events\Acknowledge\AcknowledgeCreatedEvent;
use App\Events\Acknowledge\AcknowledgeUpdatedEvent;
use App\Service\EntityHistory\EntityHistoryService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AcknowledgeListener implements EventSubscriberInterface
{

    private $em;
    private $entityHistoryService;
    private $producer;

    /**
     * AreaListener constructor.
     * @param EntityManager $em
     * @param EntityHistoryService $entityHistoryService
     */
    public function __construct(
        EntityManager $em,
        EntityHistoryService $entityHistoryService
    ) {
        $this->em = $em;
        $this->entityHistoryService = $entityHistoryService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            AcknowledgeCreatedEvent::NAME => 'onAcknowledgeCreated',
            AcknowledgeUpdatedEvent::NAME => 'onAcknowledgeUpdated'
        ];
    }

    /**
     * @param AcknowledgeUpdatedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onAcknowledgeUpdated(AcknowledgeUpdatedEvent $event)
    {
        $acknowledge = $event->getAcknowledge();
        $this->entityHistoryService->create(
            $acknowledge,
            json_encode(['status' => $acknowledge->getStatus(), 'comment' => $acknowledge->getComment()]),
            EntityHistoryTypes::ACKNOWLEDGE_UPDATED,
            $event->getUser()
        );

        $this->em->flush();
    }

    /**
     * @param AcknowledgeCreatedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onAcknowledgeCreated(AcknowledgeCreatedEvent $event)
    {
        $acknowledge = $event->getAcknowledge();
        $this->entityHistoryService->create(
            $acknowledge,
            json_encode(['status' => $acknowledge->getStatus(), 'comment' => $acknowledge->getComment()]),
            EntityHistoryTypes::ACKNOWLEDGE_CREATED
        );

        $this->em->flush();
    }
}