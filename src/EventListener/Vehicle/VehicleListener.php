<?php

namespace App\EventListener\Vehicle;

use App\Entity\BillingEntityHistory;
use App\Entity\EventLog\EventLog;
use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use App\Entity\Vehicle;
use App\Enums\EntityHistoryTypes;
use App\Events\Device\DeviceInstalledEvent;
use App\Events\Vehicle\VehicleArchivedEvent;
use App\Events\Vehicle\VehicleCreatedEvent;
use App\Events\Vehicle\VehicleDeletedEvent;
use App\Events\Vehicle\VehicleRestoredEvent;
use App\Events\Vehicle\VehicleStatusChangedEvent;
use App\Events\Vehicle\VehicleUpdatedEvent;
use App\Service\Billing\BillingEntityHistoryService;
use App\Service\EntityHistory\EntityHistoryService;
use App\Service\MapService\MapServiceInterface;
use App\Service\MapService\MapServiceResolver;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class VehicleListener implements EventSubscriberInterface
{
    private $listenedEvents = [];

    private $em;
    private $entityHistoryService;
    private BillingEntityHistoryService $billingEntityHistoryService;
    private MapServiceResolver $mapServiceResolver;
    private MapServiceInterface $mapService;
    private NotificationEventDispatcher $notificationDispatcher;

    public function __construct(
        EntityManager $em,
        EntityHistoryService $entityHistoryService,
        BillingEntityHistoryService $billingEntityHistoryService,
        MapServiceResolver $mapServiceResolver,
        NotificationEventDispatcher $notificationDispatcher,
        private readonly ObjectPersister $reminderObjectPersister,
        private readonly ObjectPersister $documentObjectPersister,
    ) {
        $this->em = $em;
        $this->entityHistoryService = $entityHistoryService;
        $this->billingEntityHistoryService = $billingEntityHistoryService;
        $this->mapService = $mapServiceResolver->getInstance();
        $this->notificationDispatcher = $notificationDispatcher;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            VehicleCreatedEvent::NAME => 'onVehicleCreated',
            VehicleUpdatedEvent::NAME => 'onVehicleUpdated',
            VehicleDeletedEvent::NAME => 'onVehicleDeleted',
            DeviceInstalledEvent::NAME => 'onDeviceInstalled',
            VehicleRestoredEvent::NAME => 'onVehicleRestored',
            VehicleArchivedEvent::NAME => 'onVehicleArchived',
            VehicleStatusChangedEvent::NAME => 'onVehicleStatusChanged',
        ];
    }

    /**
     * @param string $event
     * @return bool
     */
    protected function isEventWas(string $event): bool
    {
        return in_array($event, $this->listenedEvents, true);
    }

    public function onVehicleUpdated(VehicleUpdatedEvent $event)
    {
        $vehicle = $event->getVehicle();
        $vehicle->setUpdatedAt(new \DateTime());
        $this->entityHistoryService->create(
            $vehicle,
            $vehicle->getUpdatedAt() ? $vehicle->getUpdatedAt()->getTimestamp() : Carbon::now('UTC')->getTimestamp(),
            EntityHistoryTypes::VEHICLE_UPDATED,
            $vehicle->getUpdatedBy()
        );

        $this->em->flush();
        $this->updateDocumentEs($vehicle);
        $this->updateReminderEs($vehicle);
    }

    public function onVehicleDeleted(VehicleDeletedEvent $event)
    {
        $vehicle = $event->getVehicle();
        $this->entityHistoryService->create(
            $vehicle,
            time(),
            EntityHistoryTypes::VEHICLE_DELETED,
            $vehicle->getUpdatedBy()
        );

        $lastBillingEntity = $this->billingEntityHistoryService->getLastRecord(
            $vehicle->getId(),
            BillingEntityHistory::ENTITY_VEHICLE,
            BillingEntityHistory::TYPE_ARCHIVE
        );
        if ($lastBillingEntity) {
            $this->billingEntityHistoryService->update(['dateTo' => new \DateTime()], $lastBillingEntity);
        }

        $lastBillingEntity = $this->billingEntityHistoryService->getLastRecord(
            $vehicle->getId(),
            BillingEntityHistory::ENTITY_VEHICLE,
            BillingEntityHistory::TYPE_CREATE_DELETE
        );
        $this->billingEntityHistoryService->update(['dateTo' => new \DateTime()], $lastBillingEntity);

        $this->em->flush();

        $this->updateDocumentEs($vehicle);
        $this->updateReminderEs($vehicle);
    }

    public function onVehicleCreated(VehicleCreatedEvent $event)
    {
        $vehicle = $event->getVehicle();
        $this->entityHistoryService->create(
            $vehicle,
            $vehicle->getCreatedAt()->getTimestamp(),
            EntityHistoryTypes::VEHICLE_CREATED,
            $vehicle->getCreatedBy()
        );

        $this->billingEntityHistoryService->create([
            'entityId' => $vehicle->getId(),
            'entity' => BillingEntityHistory::ENTITY_VEHICLE,
            'type' => BillingEntityHistory::TYPE_CREATE_DELETE,
            'dateFrom' => new \DateTime(),
            'team' => $vehicle->getTeam()
        ]);

        $this->em->flush();
    }

    public function onVehicleRestored(VehicleRestoredEvent $event)
    {
        $vehicle = $event->getVehicle();

        $lastBillingEntity = $this->billingEntityHistoryService->getLastRecord(
            $vehicle->getId(),
            BillingEntityHistory::ENTITY_VEHICLE,
            BillingEntityHistory::TYPE_ARCHIVE
        );
        $this->billingEntityHistoryService->update(['dateTo' => new \DateTime()], $lastBillingEntity);
    }

    public function onVehicleArchived(VehicleArchivedEvent $event)
    {
        $vehicle = $event->getVehicle();

        $this->billingEntityHistoryService->create([
            'entityId' => $vehicle->getId(),
            'entity' => BillingEntityHistory::ENTITY_VEHICLE,
            'type' => BillingEntityHistory::TYPE_ARCHIVE,
            'dateFrom' => new \DateTime(),
            'team' => $vehicle->getTeam()
        ]);

        $this->em->flush();
    }

    public function onDeviceInstalled(DeviceInstalledEvent $event)
    {
        $vehicle = $event->getDevice()->getVehicle();
        $vehicle->setStatus(Vehicle::STATUS_ONLINE);

        $this->em->flush();
    }

    public function onVehicleStatusChanged(VehicleStatusChangedEvent $eventData)
    {
        $vehicle = $eventData->getVehicle();
        if (!in_array($vehicle->getStatus(), [Vehicle::STATUS_OFFLINE, Vehicle::STATUS_ONLINE])) {
            return;
        }

        //set offline duration for the last event log
        if ($vehicle->getStatus() === Vehicle::STATUS_ONLINE) {
            $event = $this->em->getRepository(Event::class)->getEventByName(Event::VEHICLE_OFFLINE);

            $ntfCount = $this->em->getRepository(Notification::class)
                ->getNtfCountByTeamAndEvent($vehicle->getTeam(), $event);
            if (!$ntfCount) {
                return;
            }

            $lastEventLogOffline = $this->em->getRepository(EventLog::class)
                ->getLastEventLogByDetailsId($event, $vehicle->getId());
            if ($lastEventLogOffline) {
                $details = $lastEventLogOffline->getDetails();
                if (!($details['context']['duration'] ?? null)) {
                    $details['context']['duration'] = (new Carbon())->diffInSeconds($lastEventLogOffline->getEventDate());
                    if ($details['context']['gpsStatusDurationSetting'] ?? null) {
                        $details['context']['duration'] += $details['context']['gpsStatusDurationSetting'];
                    }

                    $lastEventLogOffline->setDetails($details);
                    $this->em->flush();
                }
            }
            return;
        }

        $coordinates = $vehicle->getLastTrackerRecord()?->toArrayCoordinates();
        if ($coordinates && ($coordinates['lat'] ?? null) && $coordinates['lng'] ?? null) {
            $address = $this->mapService->getLocationByCoordinates($coordinates['lat'], $coordinates['lng']);
        } else {
            $address = null;
        }
        $context = [
            'lastCoordinates' => $vehicle->getLastTrackerRecord()?->toArrayCoordinates(),
            'address' => $address,
            'duration' => null,
            'gpsStatusDurationSetting' => $eventData->getData()['gpsStatusDurationSetting'] ?? 0
        ];

        $this->notificationDispatcher
            ->dispatch($vehicle->getEventNameByStatus(), $vehicle, new \DateTime(), $context);
    }

    private function updateDocumentEs(Vehicle $vehicle): void
    {
        $documents = $vehicle->getDocuments() ?? [];
        foreach ($documents as $document) {
            $this->documentObjectPersister->replaceOne($document);
        }
    }

    private function updateReminderEs(Vehicle $vehicle): void
    {
        $reminders = $vehicle->getReminders() ?? [];
        foreach ($reminders as $reminder) {
            $this->reminderObjectPersister->replaceOne($reminder);
        }
    }
}