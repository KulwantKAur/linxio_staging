<?php

namespace App\EventListener\User;

use App\Entity\User;
use App\Enums\EntityHistoryTypes;
use App\Events\User\Driver\DriverUnassignedFromVehicleEvent;
use App\Events\User\Login\UserLoginEvent;
use App\Events\User\UserAddedToGroupEvent;
use App\Events\User\UserArchivedEvent;
use App\Events\User\UserCreatedEvent;
use App\Events\User\UserDeletedEvent;
use App\Events\User\UserPreUpdatedEvent;
use App\Events\User\UserRemovedFromGroupEvent;
use App\Events\User\UserUpdatedEvent;
use App\Service\Device\DeviceCommandService;
use App\Service\EntityHistory\EntityHistoryService;
use App\Service\Sensor\SensorService;
use App\Util\IpHelper;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserListener implements EventSubscriberInterface
{
    private $listenedEvents = [];

    private $em;
    private $entityHistoryService;
    private $tokenStorage;
    private $sensorService;

    public function __construct(
        private DeviceCommandService $deviceCommandService,
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
            UserLoginEvent::EVENT_BLOCKED_USER_LOGIN => 'processingBlockedUserLogin',
            UserLoginEvent::EVENT_NEW_USER_LOGIN => 'processingNewUserLogin',
            UserLoginEvent::EVENT_USER_LOGIN => 'onUserLogin',
            'kernel.controller' => 'filterController',
            UserCreatedEvent::NAME => 'onUserCreated',
            UserUpdatedEvent::NAME => 'onUserUpdated',
            UserDeletedEvent::NAME => 'onUserDeleted',
            UserArchivedEvent::NAME => 'onUserArchived',
            UserLoginEvent::EVENT_USER_REFRESH_TOKEN => 'onUserRefreshToken',
            UserPreUpdatedEvent::NAME => 'onUserPreUpdated',
            UserAddedToGroupEvent::NAME => 'onUserAddedToGroup',
            UserRemovedFromGroupEvent::NAME => 'onUserRemovedFromGroup',
            DriverUnassignedFromVehicleEvent::NAME => 'onDriverUnassignedFromVehicle',
        ];
    }

    /**
     * @param UserLoginEvent $event
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function processingNewUserLogin(UserLoginEvent $event)
    {
        $this->addEventToListened(UserLoginEvent::EVENT_NEW_USER_LOGIN);
        $event->getUser()->activate();
        $this->em->flush();
    }

    public function processingBlockedUserLogin()
    {
        $this->addEventToListened(UserLoginEvent::EVENT_BLOCKED_USER_LOGIN);
    }

    /**
     * @param ControllerEvent $event
     */
    public function filterController(ControllerEvent $event)
    {
        $this->addEventToListened('kernel.controller');

        if ($this->isEventWas(UserLoginEvent::EVENT_BLOCKED_USER_LOGIN)) {
            /** @var User $user */
            $user = $this->tokenStorage->getToken()->getUser();

            $event->setController(
                function () use ($user) {
                    return new JsonResponse(
                        [
                            'blocked' => true,
                            'message' => $user->getBlockingMessage(),
                        ]
                    );
                }
            );
        }
    }

    /**
     * @param string $event
     * @return bool
     */
    protected function isEventWas(string $event): bool
    {
        return in_array($event, $this->listenedEvents, true);
    }

    /**
     * @param $event
     */
    private function addEventToListened(string $event): void
    {
        $this->listenedEvents[] = $event;
    }

    /**
     * @param UserLoginEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onUserLogin(UserLoginEvent $event)
    {
        $user = $event->getUser();
        $user->setLastLoggedAt(new \DateTime());
        $this->em->flush();

        $this->entityHistoryService->create(
            $user,
            json_encode(['ts' => time(), 'ip' => IpHelper::getIp()]),
            EntityHistoryTypes::USER_LAST_LOGIN,
            $user->getCreatedBy()
        );
    }

    /**
     * @param UserUpdatedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onUserUpdated(UserUpdatedEvent $event)
    {
        $user = $event->getUser();
        $this->entityHistoryService->create(
            $user,
            $user->getUpdatedAt() ? $user->getUpdatedAt()->getTimestamp() : Carbon::now('UTC'),
            EntityHistoryTypes::USER_UPDATED,
            null,
            $user->getUpdatedBy() ? $user->getUpdatedBy()->getId() : null
        );

        $this->em->flush();
    }

    /**
     * @param UserDeletedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onUserDeleted(UserDeletedEvent $event)
    {
        /** @var User $user */
        $user = $event->getUser();
        $this->entityHistoryService->create(
            $user,
            $user->getUpdatedAt()->getTimestamp(),
            EntityHistoryTypes::USER_DELETED,
            null,
            $user->getUpdatedBy()->getId()
        );

        $this->em->flush();
    }

    public function onUserArchived(UserArchivedEvent $event)
    {
        /** @var User $user */
        $user = $event->getUser();
        $this->entityHistoryService->create(
            $user,
            $user->getUpdatedAt()->getTimestamp(),
            EntityHistoryTypes::USER_ARCHIVED,
            null,
            $user->getUpdatedBy()->getId()
        );

        $this->em->flush();
    }

    /**
     * @param UserCreatedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function onUserCreated(UserCreatedEvent $event)
    {
        $user = $event->getUser();
        $this->entityHistoryService->create(
            $user,
            $user->getCreatedAt()->getTimestamp(),
            EntityHistoryTypes::USER_CREATED,
            $user->getCreatedBy()
        );
        $this->entityHistoryService->create($user, $user->getStatus(), EntityHistoryTypes::USER_STATUS);

        if ($user->isDriverClientOrDualAccount() && $user->getDriverSensorId()) {
            $sensor = $this->sensorService
                ->createTopflytechDriverSensorIdIfNotExists($user->getDriverSensorId(), $user->getCreatedBy());
            // @todo check if transaction is ok
            $this->sensorService->updateVehicleDevicesWithDriverSensorId(
                $user,
                $user->getCreatedBy(),
                $user->getVehiclesFromUserGroups()
            );
            $user->setSensor($sensor);
        }

        $this->em->flush();
    }

    public function onUserRefreshToken(UserLoginEvent $event)
    {
        /** @var User $user */
        $user = $event->getUser();
        $this->entityHistoryService->create(
            $user,
            time(),
            EntityHistoryTypes::USER_REFRESH_TOKEN,
            null,
            $user->getId()
        );

        $this->em->flush();
    }

    public function onUserPreUpdated(UserPreUpdatedEvent $event)
    {
        /** @var User $user */
        $user = $event->getUser();

        if (
            $user->isDriverClientOrDualAccount()
            && $this->sensorService->isEntityFieldChanged($this->em->getUnitOfWork(), $user, 'driverSensorId')
        ) {
            $event->setIsDriverSensorChanged(true);
            $oldDriverSensorId = $this->sensorService
                ->getEntityFieldOldValue($this->em->getUnitOfWork(), $user, 'driverSensorId');
            $event->setOldDriverSensorId($oldDriverSensorId);
            $newDriverSensorId = $user->getDriverSensorId();
            $event->setNewDriverSensorId($newDriverSensorId);
        }
    }

    /**
     * @param UserAddedToGroupEvent $event
     * @throws \Exception
     */
    public function onUserAddedToGroup(UserAddedToGroupEvent $event)
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
     * @param UserRemovedFromGroupEvent $event
     * @throws \Exception
     */
    public function onUserRemovedFromGroup(UserRemovedFromGroupEvent $event)
    {
        $user = $event->getUser();

        if ($user->isDriverClientOrDualAccount()) {
            $group = $event->getGroup();
            $driverSensorId = ($this->sensorService
                ->isEntityFieldChanged($this->em->getUnitOfWork(), $user, 'driverSensorId')
            )
                ? $this->sensorService->getEntityFieldOldValue($this->em->getUnitOfWork(), $user, 'driverSensorId')
                : $user->getDriverSensorId();
            $this->sensorService->removeDriverSensorIdFromVehicleDevices(
                $driverSensorId,
                $user->getUpdatedBy(),
                $group->getVehiclesByScope()->getValues()
            );
        }
    }

    public function onDriverUnassignedFromVehicle(DriverUnassignedFromVehicleEvent $event)
    {
        $driver = $event->getDriver();
        $vehicle = $event->getVehicle();
        $device = $vehicle->getDevice();

        if ($driver->isDriverClientOrDualAccount() && $device) {
            $this->deviceCommandService->topflytechRelayDevice($device);
        }
    }
}