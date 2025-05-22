<?php

namespace App\EventListener\Sensor;

use App\Entity\User;
use App\Enums\EntityHistoryTypes;
use App\Events\Sensor\SensorCreatedEvent;
use App\Events\Sensor\SensorDeletedEvent;
use App\Events\Sensor\SensorUpdatedEvent;
use App\Events\Sensor\SensorUserUpdatedEvent;
use App\Events\User\UserPreUpdatedEvent;
use App\Service\EntityHistory\EntityHistoryService;
use App\Service\Sensor\SensorService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SensorListener implements EventSubscriberInterface
{
    private $listenedEvents = [];

    private $em;
    private $entityHistoryService;
    private $sensorService;

    /**
     * @param User $user
     * @param User $currentUser
     * @param UserPreUpdatedEvent $event
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function updateSensorDependencies(User $user, User $currentUser, UserPreUpdatedEvent $event)
    {
        if ($event->isDriverSensorChanged()) {
            $newDriverSensorId = $event->getNewDriverSensorId();
            $oldDriverSensorId = $event->getOldDriverSensorId();

            if ($sensor = $user->getSensor()) {
                if ($newDriverSensorId) {
                    $this->sensorService->updateSensor(
                        $sensor,
                        ['sensorId' => $newDriverSensorId],
                        $currentUser
                    );
                } else {
                    $user->setSensor(null);
                    $user->setDriverSensorId(null);
                    $this->sensorService->deleteSensorAndDependencies($sensor, $currentUser);
                }
            } else {
                if ($newDriverSensorId) {
                    // @todo why topflytech?
                    $sensor = $this->sensorService
                        ->createTopflytechDriverSensorIdIfNotExists($newDriverSensorId, $currentUser);
                    $user->setSensor($sensor);
                }
            }

            // @todo check if transaction is ok
            $this->sensorService->updateVehicleDevicesWithDriverSensorId(
                $user,
                $user->getUpdatedBy(),
                $user->getVehiclesFromUserGroups(),
                $oldDriverSensorId
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
     * SensorListener constructor.
     * @param EntityManager $em
     * @param EntityHistoryService $entityHistoryService
     * @param SensorService $sensorService
     */
    public function __construct(
        EntityManager $em,
        EntityHistoryService $entityHistoryService,
        SensorService $sensorService
    ) {
        $this->em = $em;
        $this->entityHistoryService = $entityHistoryService;
        $this->sensorService = $sensorService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            SensorCreatedEvent::NAME => 'onSensorCreated',
            SensorUpdatedEvent::NAME => 'onSensorUpdated',
            SensorDeletedEvent::NAME => 'onSensorDeleted',
            SensorUserUpdatedEvent::NAME => 'onSensorUserUpdated',
        ];
    }

    /**
     * @param SensorUpdatedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onSensorUpdated(SensorUpdatedEvent $event)
    {
        $sensor = $event->getSensor();
        $this->entityHistoryService->create(
            $sensor,
            $sensor->getUpdatedAt() ? $sensor->getUpdatedAt()->getTimestamp() : Carbon::now('UTC')->getTimestamp(),
            EntityHistoryTypes::SENSOR_UPDATED,
            $sensor->getUpdatedBy()
        );

        $this->em->flush();
    }

    /**
     * @param SensorDeletedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onSensorDeleted(SensorDeletedEvent $event)
    {
        $sensor = $event->getSensor();
        $this->entityHistoryService->create(
            $sensor,
            time(),
            EntityHistoryTypes::SENSOR_DELETED,
            $sensor->getUpdatedBy()
        );

        $this->em->flush();
    }

    /**
     * @param SensorCreatedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onSensorCreated(SensorCreatedEvent $event)
    {
        $sensor = $event->getSensor();
        $this->entityHistoryService->create(
            $sensor,
            $sensor->getCreatedAt()->getTimestamp(),
            EntityHistoryTypes::SENSOR_CREATED,
            $sensor->getCreatedBy()
        );

        $this->em->flush();
    }

    /**
     * @param SensorUserUpdatedEvent $event
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onSensorUserUpdated(SensorUserUpdatedEvent $event)
    {
        $user = $event->getUser();
        $currentUser = $event->getCurrentUser();
        $userPreUpdatedEvent = $event->getUserPreUpdatedEvent();
        $this->updateSensorDependencies($user, $currentUser, $userPreUpdatedEvent);
        $this->em->flush();
    }
}
