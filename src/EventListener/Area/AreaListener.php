<?php

namespace App\EventListener\Area;

use App\Entity\Area;
use App\Enums\EntityHistoryTypes;
use App\Events\Area\AreaCreatedEvent;
use App\Events\Area\AreaDeletedEvent;
use App\Events\Area\AreaUpdatedEvent;
use App\Events\Area\CheckAreaEvent;
use App\Service\Area\CheckAreaConsumer;
use App\Service\Area\CheckAreaQueueMessage;
use App\Service\Device\DeviceService;
use App\Service\EntityHistory\EntityHistoryService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AreaListener implements EventSubscriberInterface
{

    private EntityManager $em;
    private EntityHistoryService $entityHistoryService;
    private DeviceService $deviceService;
    private Producer $producer;

    /**
     * AreaListener constructor.
     * @param EntityManager $em
     * @param EntityHistoryService $entityHistoryService
     * @param DeviceService $deviceService
     * @param TokenStorageInterface $tokenStorage
     * @param Producer $producer
     */
    public function __construct(
        EntityManager $em,
        EntityHistoryService $entityHistoryService,
        DeviceService $deviceService,
        Producer $producer
    ) {
        $this->em = $em;
        $this->entityHistoryService = $entityHistoryService;
        $this->deviceService = $deviceService;
        $this->producer = $producer;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            AreaCreatedEvent::NAME => 'onAreaCreated',
            AreaUpdatedEvent::NAME => 'onAreaUpdated',
            AreaDeletedEvent::NAME => 'onAreaDeleted',
            CheckAreaEvent::NAME => 'processingCheckAreaEvent'
        ];
    }

    /**
     * @param AreaUpdatedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onAreaUpdated(AreaUpdatedEvent $event)
    {
        $area = $event->getArea();
        $this->entityHistoryService->create(
            $area,
            $area->getUpdatedAt() ? $area->getUpdatedAt()->getTimestamp() : Carbon::now('UTC')->getTimestamp(),
            EntityHistoryTypes::AREA_UPDATED,
            $area->getUpdatedBy()
        );

        $this->em->flush();
    }

    /**
     * @param AreaDeletedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onAreaDeleted(AreaDeletedEvent $event)
    {
        $area = $event->getArea();
        $this->entityHistoryService->create(
            $area,
            time(),
            EntityHistoryTypes::AREA_DELETED,
            $area->getUpdatedBy()
        );

        $this->em->flush();
    }

    /**
     * @param AreaCreatedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onAreaCreated(AreaCreatedEvent $event)
    {
        $area = $event->getArea();
        $this->entityHistoryService->create(
            $area,
            $area->getCreatedAt()->getTimestamp(),
            EntityHistoryTypes::AREA_CREATED,
            $area->getCreatedBy()
        );

        if ($area->getStatus()) {
            $this->entityHistoryService->create($area, $area->getStatus(), EntityHistoryTypes::AREA_STATUS);
        }

        $this->em->flush();
    }

    /**
     * @param CheckAreaEvent $event
     * @return void
     * @throws \Exception
     */
    public function processingCheckAreaEvent(CheckAreaEvent $event): void
    {
        $area = $this->em->getRepository(Area::class)->count([
            'team' => $event->getDevice()->getTeam(),
            'status' => Area::STATUS_ACTIVE
        ]);

        if (!$area) {
            return;
        }

        $eventMessage = new CheckAreaQueueMessage($event->getDevice(), $event->getTrackerHistoryData());
        $routingKey = $this->deviceService->getRoutingKeyByDeviceIdQueuesNumberAndPrefix(
            $event->getDevice()->getId(),
            CheckAreaConsumer::QUEUES_NUMBER,
            CheckAreaConsumer::ROUTING_KEY_PREFIX
        );
        $this->producer->publish($eventMessage, $routingKey);
    }
}
