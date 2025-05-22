<?php

namespace App\EventListener\UserGroup;

use App\Entity\Vehicle;
use App\Enums\EntityHistoryTypes;
use App\Events\UserGroup\UserAddedToUserGroupEvent;
use App\Events\UserGroup\UserGroupChangedScopeEvent;
use App\Events\UserGroup\UserGroupCreatedEvent;
use App\Events\UserGroup\UserGroupDeletedEvent;
use App\Events\UserGroup\UserGroupUpdatedEvent;
use App\Events\UserGroup\UserRemovedFromUserGroupEvent;
use App\Service\EntityHistory\EntityHistoryService;
use App\Service\Sensor\SensorService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserGroupListener implements EventSubscriberInterface
{
    private $listenedEvents = [];

    private $em;
    private $entityHistoryService;
    private $tokenStorage;
    private $sensorService;

    /**
     * UserGroupListener constructor.
     * @param EntityManager $em
     * @param EntityHistoryService $entityHistoryService
     * @param TokenStorageInterface $tokenStorage
     * @param SensorService $sensorService
     */
    public function __construct(
        EntityManager $em,
        EntityHistoryService $entityHistoryService,
        TokenStorageInterface $tokenStorage,
        SensorService $sensorService
    ) {
        $this->em = $em;
        $this->entityHistoryService = $entityHistoryService;
        $this->tokenStorage = $tokenStorage;
        $this->sensorService = $sensorService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            UserGroupCreatedEvent::NAME => 'onUserGroupCreated',
            UserGroupUpdatedEvent::NAME => 'onUserGroupUpdated',
            UserGroupDeletedEvent::NAME => 'onUserGroupDeleted',
            UserAddedToUserGroupEvent::NAME => 'onUserAddedToUserGroup',
            UserRemovedFromUserGroupEvent::NAME => 'onUserRemovedFromUserGroup',
            UserGroupChangedScopeEvent::NAME => 'onUserGroupChangedScope',
        ];
    }

    /**
     * @param UserGroupUpdatedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onUserGroupUpdated(UserGroupUpdatedEvent $event)
    {
        $vehicleGroup = $event->getUserGroup();
        $this->entityHistoryService->create(
            $vehicleGroup,
            Carbon::now('UTC')->getTimestamp(),
            EntityHistoryTypes::USER_GROUP_UPDATED,
            $event->getUser()
        );

        $this->em->flush();
    }

    /**
     * @param UserGroupCreatedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onUserGroupCreated(UserGroupCreatedEvent $event)
    {
        $vehicleGroup = $event->getUserGroup();
        $this->entityHistoryService->create(
            $vehicleGroup,
            Carbon::now('UTC')->getTimestamp(),
            EntityHistoryTypes::USER_GROUP_CREATED,
            $event->getUser()
        );

        $this->em->flush();
    }

    /**
     * @param UserGroupDeletedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onUserGroupDeleted(UserGroupDeletedEvent $event)
    {
        $vehicleGroup = $event->getUserGroup();
        $this->entityHistoryService->create(
            $vehicleGroup,
            Carbon::now('UTC')->getTimestamp(),
            EntityHistoryTypes::USER_GROUP_DELETED,
            $event->getUser()
        );

        $this->em->flush();
    }

    /**
     * @param UserAddedToUserGroupEvent $event
     * @throws \Exception
     */
    public function onUserAddedToUserGroup(UserAddedToUserGroupEvent $event)
    {
        $user = $event->getUser();

        if ($user->isDriverClientOrDualAccount() && $user->getDriverSensorId()) {
            $group = $event->getGroup();
            $this->sensorService->addDriverSensorIdToVehicleDevices(
                $user->getDriverSensorId(),
                $user->getUpdatedBy(),
                $group->getVehiclesByScope()->getValues()
            );
        }
    }

    /**
     * @param UserRemovedFromUserGroupEvent $event
     * @throws \Exception
     */
    public function onUserRemovedFromUserGroup(UserRemovedFromUserGroupEvent $event)
    {
        $user = $event->getUser();

        if ($user->isDriverClientOrDualAccount() && $user->getDriverSensorId()) {
            $group = $event->getGroup();
            $this->sensorService->removeDriverSensorIdFromVehicleDevices(
                $user->getDriverSensorId(),
                $user->getUpdatedBy(),
                $group->getVehiclesByScope()->getValues()
            );
        }
    }

    /**
     * @param UserGroupChangedScopeEvent $event
     * @throws \Exception
     */
    public function onUserGroupChangedScope(UserGroupChangedScopeEvent $event)
    {
        $currentUser = $event->getUser();
        $userGroup = $event->getUserGroup();
        $users = $userGroup->getUsers();
        $vehiclesIdsToAdd = $event->getVehiclesIdsToAdd();
        $vehiclesToAdd = $this->em->getRepository(Vehicle::class)->getVehiclesByIds($vehiclesIdsToAdd);
        $vehiclesIdsToRemove = $event->getVehiclesIdsToRemove();
        $vehiclesToRemove = $this->em->getRepository(Vehicle::class)->getVehiclesByIds($vehiclesIdsToRemove);

        if ($vehiclesToAdd || $vehiclesToRemove) {
            foreach ($users as $user) {
                if ($user->isDriverClientOrDualAccount() && $driverSensorId = $user->getDriverSensorId()) {
                    $this->sensorService->addDriverSensorIdToVehicleDevices(
                        $driverSensorId,
                        $currentUser,
                        $vehiclesToAdd
                    );
                    $this->sensorService->removeDriverSensorIdFromVehicleDevices(
                        $driverSensorId,
                        $currentUser,
                        $vehiclesToRemove
                    );
                }
            }
        }
    }
}