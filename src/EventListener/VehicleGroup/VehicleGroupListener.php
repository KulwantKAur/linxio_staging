<?php

namespace App\EventListener\VehicleGroup;

use App\Entity\Vehicle;
use App\Entity\VehicleGroup;
use App\Enums\EntityHistoryTypes;
use App\Events\VehicleGroup\VehicleAddedToVehicleGroupEvent;
use App\Events\VehicleGroup\VehicleGroupCreatedEvent;
use App\Events\VehicleGroup\VehicleGroupDeletedEvent;
use App\Events\VehicleGroup\VehicleGroupUpdatedEvent;
use App\Events\VehicleGroup\VehicleRemovedFromVehicleGroupEvent;
use App\Service\EntityHistory\EntityHistoryService;
use App\Service\Sensor\SensorService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class VehicleGroupListener implements EventSubscriberInterface
{
    private $listenedEvents = [];

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
            VehicleGroupCreatedEvent::NAME => 'onVehicleGroupCreated',
            VehicleGroupUpdatedEvent::NAME => 'onVehicleGroupUpdated',
            VehicleGroupDeletedEvent::NAME => 'onVehicleGroupDeleted',
            VehicleAddedToVehicleGroupEvent::NAME => 'onVehicleAddedToVehicleGroup',
            VehicleRemovedFromVehicleGroupEvent::NAME => 'onVehicleRemovedFromVehicleGroup',
        ];
    }

    protected function isEventWas(string $event): bool
    {
        return in_array($event, $this->listenedEvents, true);
    }

    public function onVehicleGroupUpdated(VehicleGroupUpdatedEvent $event)
    {
        $vehicleGroup = $event->getVehicleGroup();
        $this->entityHistoryService->create(
            $vehicleGroup,
            Carbon::now('UTC')->getTimestamp(),
            EntityHistoryTypes::VEHICLE_GROUP_UPDATED,
            $event->getUser()
        );

        $this->em->flush();

        $this->updateDocumentEs($vehicleGroup);
        $this->updateReminderEs($vehicleGroup);
    }

    public function onVehicleGroupCreated(VehicleGroupCreatedEvent $event)
    {
        $vehicleGroup = $event->getVehicleGroup();
        $this->entityHistoryService->create(
            $vehicleGroup,
            Carbon::now('UTC')->getTimestamp(),
            EntityHistoryTypes::VEHICLE_GROUP_CREATED,
            $event->getUser()
        );

        $this->em->flush();

        $this->updateDocumentEs($vehicleGroup);
        $this->updateReminderEs($vehicleGroup);
    }

    public function onVehicleGroupDeleted(VehicleGroupDeletedEvent $event)
    {
        $vehicleGroup = $event->getVehicleGroup();
        $this->entityHistoryService->create(
            $vehicleGroup,
            Carbon::now('UTC')->getTimestamp(),
            EntityHistoryTypes::VEHICLE_GROUP_DELETED,
            $event->getUser()
        );

        $this->em->flush();

        $this->updateDocumentEs($vehicleGroup);
        $this->updateReminderEs($vehicleGroup);
    }

    public function onVehicleAddedToVehicleGroup(VehicleAddedToVehicleGroupEvent $event)
    {
        $vehicle = $event->getVehicle();

        if ($vehicle->getDevice()) {
            $vehicleGroup = $event->getVehicleGroup();
            $currentUser = $event->getCurrentUser();
            $users = $vehicleGroup->getUsersFromUserGroups();

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

    public function onVehicleRemovedFromVehicleGroup(VehicleRemovedFromVehicleGroupEvent $event)
    {
        $vehicle = $event->getVehicle();
        foreach ($vehicle->getDocuments() as $document) {
            $this->documentObjectPersister->replaceOne($document);
        }

        if ($vehicle->getDevice()) {
            $vehicleGroup = $event->getVehicleGroup();
            $currentUser = $event->getCurrentUser();
            $users = $vehicleGroup->getUsersFromUserGroups();

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

    private function updateDocumentEs(VehicleGroup $vehicleGroup): void
    {
        /** @var Vehicle $vehicle */
        foreach ($vehicleGroup->getVehiclesEntities() as $vehicle) {
            foreach ($vehicle->getDocuments() as $document) {
                $this->documentObjectPersister->replaceOne($document);
            }
        }
    }

    private function updateReminderEs(VehicleGroup $vehicleGroup): void
    {
        foreach ($vehicleGroup->getVehiclesEntities() as $vehicle) {
            foreach ($vehicle->getReminders() ?? [] as $reminder) {
                $this->reminderObjectPersister->replaceOne($reminder);
            }
        }
    }
}