<?php

namespace App\EventListener\Reseller;

use App\Enums\EntityHistoryTypes;
use App\Events\Reseller\ResellerCreatedEvent;
use App\Events\Reseller\ResellerUpdatedEvent;
use App\Service\EntityHistory\EntityHistoryService;
use Doctrine\ORM\EntityManagerInterface;

class ResellerListener
{
    private $em;
    private $entityHistoryService;

    /**
     * ResellerListener constructor.
     * @param EntityManagerInterface $em
     * @param EntityHistoryService $entityHistoryService
     */
    public function __construct(EntityManagerInterface $em, EntityHistoryService $entityHistoryService)
    {
        $this->em = $em;
        $this->entityHistoryService = $entityHistoryService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ResellerCreatedEvent::NAME => 'onResellerCreated',
            ResellerUpdatedEvent::NAME => 'onResellerUpdated',
        ];
    }

    /**
     * @param ResellerUpdatedEvent $event
     */
    public function onResellerUpdated(ResellerUpdatedEvent $event)
    {
        $reseller = $event->getReseller();
        $this->entityHistoryService->create(
            $reseller,
            $reseller->getUpdatedAt()->getTimestamp(),
            EntityHistoryTypes::RESELLER_UPDATED,
            $reseller->getUpdatedBy()
        );

        $this->entityHistoryService->create($reseller, $reseller->getStatus(), EntityHistoryTypes::RESELLER_STATUS);

        $this->em->flush();
    }

    /**
     * @param ResellerCreatedEvent $event
     */
    public function onResellerCreated(ResellerCreatedEvent $event)
    {
        $reseller = $event->getReseller();
        $this->entityHistoryService->create(
            $reseller,
            $reseller->getCreatedAt()->getTimestamp(),
            EntityHistoryTypes::RESELLER_CREATED,
            $reseller->getCreatedBy()
        );

        $this->em->flush();
    }
}