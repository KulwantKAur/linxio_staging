<?php

namespace App\EventListener\Depot;

use App\Entity\Depot;
use App\Entity\Vehicle;
use App\Enums\EntityHistoryTypes;
use App\Events\Depot\DepotCreatedEvent;
use App\Events\Depot\DepotDeletedEvent;
use App\Events\Depot\DepotUpdatedEvent;
use App\Events\Depot\VehicleAddedToDepotEvent;
use App\Events\Depot\VehicleRemovedFromDepotEvent;
use App\Service\EntityHistory\EntityHistoryService;
use App\Service\Sensor\SensorService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DepotListener implements EventSubscriberInterface
{
    private $em;
    private $entityHistoryService;
    private $tokenStorage;
    private $sensorService;

    public function __construct(
        EntityManager $em,
        EntityHistoryService $entityHistoryService,
        TokenStorageInterface $tokenStorage,
        SensorService $sensorService,
        private readonly ObjectPersister $reminderObjectPersister,
        private readonly ObjectPersister $documentObjectPersister,
    ) {
        $this->em = $em;
        $this->entityHistoryService = $entityHistoryService;
        $this->tokenStorage = $tokenStorage;
        $this->sensorService = $sensorService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DepotCreatedEvent::NAME => 'onDepotCreated',
            DepotUpdatedEvent::NAME => 'onDepotUpdated',
            DepotDeletedEvent::NAME => 'onDepotDeleted',
            VehicleAddedToDepotEvent::NAME => 'onVehicleAddedToDepot',
            VehicleRemovedFromDepotEvent::NAME => 'onVehicleRemovedFromDepot',
        ];
    }

    public function onDepotUpdated(DepotUpdatedEvent $event)
    {
        $depot = $event->getDepot();
        $this->entityHistoryService->create(
            $depot,
            Carbon::now('UTC')->getTimestamp(),
            EntityHistoryTypes::DEPOT_UPDATED,
            $depot->getUpdatedBy()
        );

        $this->em->flush();

        $this->updateDocumentEs($depot);
        $this->updateReminderEs($depot);
    }

    public function onDepotCreated(DepotCreatedEvent $event)
    {
        $depot = $event->getDepot();
        $this->entityHistoryService->create(
            $depot,
            Carbon::now('UTC')->getTimestamp(),
            EntityHistoryTypes::DEPOT_CREATED,
            $depot->getCreatedBy()
        );

        $this->em->flush();

        $this->updateDocumentEs($depot);
        $this->updateReminderEs($depot);
    }

    public function onDepotDeleted(DepotDeletedEvent $event)
    {
        $depot = $event->getDepot();
        $this->entityHistoryService->create(
            $depot,
            Carbon::now('UTC')->getTimestamp(),
            EntityHistoryTypes::DEPOT_DELETED,
            null,
            $depot->getUpdatedBy()->getId()
        );

        $this->em->flush();

        $this->updateDocumentEs($depot);
        $this->updateReminderEs($depot);
    }

    public function onVehicleAddedToDepot(VehicleAddedToDepotEvent $event)
    {
        $vehicle = $event->getVehicle();

        if ($vehicle->getDevice()) {
            $depot = $event->getDepot();
            $currentUser = $event->getCurrentUser();
            $users = $depot->getUsersFromUserGroups();

            foreach ($users as $user) {
                if ($user->isDriverClientOrDualAccount() && $driverSensorId = $user->getDriverSensorId()) {
                    $this->sensorService->addDriverSensorIdToVehicleDevices(
                        $driverSensorId,
                        $currentUser,
                        [$vehicle]
                    );
                }
            }
        }
    }

    public function onVehicleRemovedFromDepot(VehicleRemovedFromDepotEvent $event)
    {
        $vehicle = $event->getVehicle();
        foreach ($vehicle->getDocuments() as $document) {
            $this->documentObjectPersister->replaceOne($document);
        }

        if ($vehicle->getDevice()) {
            $depot = $event->getDepot();
            $currentUser = $event->getCurrentUser();
            $users = $depot->getUsersFromUserGroups();

            foreach ($users as $user) {
                if ($user->isDriverClientOrDualAccount() && $driverSensorId = $user->getDriverSensorId()) {
                    $this->sensorService->removeDriverSensorIdFromVehicleDevices(
                        $driverSensorId,
                        $currentUser,
                        [$vehicle]
                    );
                }
            }
        }
    }

    private function updateDocumentEs(Depot $depot): void
    {
        /** @var Vehicle $vehicle */
        foreach ($depot->getVehicles() as $vehicle) {
            foreach ($vehicle->getDocuments() as $document) {
                $this->documentObjectPersister->replaceOne($document);
            }
        }
    }

    private function updateReminderEs(Depot $depot): void
    {
        foreach ($depot->getVehicles() as $vehicle) {
            foreach ($vehicle->getReminders() ?? [] as $reminder) {
                $this->reminderObjectPersister->replaceOne($reminder);
            }
        }
    }
}