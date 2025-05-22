<?php

namespace App\Repository\Notification;

use App\Entity\AreaHistory;
use App\Entity\BaseEntity;
use App\Entity\Notification\AcknowledgeRecipients;
use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use App\Entity\Notification\NotificationRecipients;
use App\Entity\Notification\NotificationScopes;
use App\Entity\Notification\NotificationTransports;
use App\Entity\Notification\Transport;
use App\Entity\Setting;
use App\Entity\Team;
use App\Entity\TimeZone;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistoryIO;
use App\Entity\Tracker\TrackerHistorySensor;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\Types\Types;
use \Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class NotificationRepository
 * @package App\Repository\Notification
 */
class NotificationRepository extends EntityRepository
{

    /**
     * @param Event $event
     * @param Team|null $team
     * @param \DateTime $dt
     * @param $entity
     * @param array $context
     * @param null $createdAt
     * @return mixed
     */
    public function getTeamNotifications(
        Event $event,
        ?Team $team,
        \DateTime $dt,
        $entity,
        $context = [],
        $createdAt = null
    ) {
        //for searching ntf in team timezone
        if ($team && $event->isListenerTeam()) {
            $dt = $this->getDateTimeWithTimeZone($team, $dt);
        }

        $qb = $query = $this->getEntityManager()->createQueryBuilder();

        $query = $qb
            ->select('nt')
            ->from(Notification::class, 'nt')
            ->where('nt.event = :event')
            ->andWhere('nt.status = :status')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->andX(
                        $query->expr()->lte('nt.eventTrackingTimeFrom', 'nt.eventTrackingTimeUntil'),
                        $query->expr()->between(':eventTime', 'nt.eventTrackingTimeFrom', 'nt.eventTrackingTimeUntil'),
                    ),
                    $qb->expr()->andX(
                        $query->expr()->gte('nt.eventTrackingTimeFrom', 'nt.eventTrackingTimeUntil'),
                        $query->expr()->lte(':eventTime', 'nt.eventTrackingTimeFrom'),
                        $query->expr()->lte(':eventTime', 'nt.eventTrackingTimeUntil'),
                    ),
                    $qb->expr()->andX(
                        $query->expr()->gte('nt.eventTrackingTimeFrom', 'nt.eventTrackingTimeUntil'),
                        $query->expr()->gte(':eventTime', 'nt.eventTrackingTimeFrom'),
                    )
                )
            )
            ->andWhere('CAST(nt.eventTrackingDays AS text) LIKE :eventDay')
            ->setParameter('event', $event)
            ->setParameter('status', BaseEntity::STATUS_ENABLED, Types::STRING)
            ->setParameter('eventTime', $dt->format('H:i'), Types::STRING)
            ->setParameter('eventDay', '%' . strtolower($dt->format('l')) . '%')
            ->orderBy('nt.createdAt');

        if ($team && $event->isListenerTeam()) {
            $query->andWhere('nt.listenerTeam = :team')
                ->setParameter('team', $team);
        }

        if ($createdAt) {
            $query->andWhere('nt.createdAt < :createdAt')->setParameter('createdAt', $createdAt);
        }

        if (in_array($event->getName(), [Event::VEHICLE_GEOFENCE_ENTER, Event::VEHICLE_GEOFENCE_LEAVE])) {
            $notificationId = $context['notificationId'] ?? null;
            if ($notificationId) {
                $query->andWhere('nt.id = :notificationId')->setParameter('notificationId', $notificationId);
            }
        }

        if ($event->isDeviceBattery()) {
            $query = $this->addDeviceBatteryQuery($query, $entity);
        }

        if ($event->isOverSpeed()) {
            $query = $this->addDeviceOverspeedingQuery($entity, $query, $context);
        }

        // depends 2 params - time + distance
        if ($event->isExpressionOperator() && $event->isTimeDuration() && $event->isDistance()) {
            $query = $this->addDeviceTimeDurationWithDistanceQuery($entity, $query, $context);

            $notificationId = $context['notificationId'] ?? null;
            if ($notificationId) {
                $query->andWhere('nt.id = :notificationId')->setParameter('notificationId', $notificationId);
            }
        } else {
            if ($event->isTimeDuration()) {
                $timeDuration = (method_exists($entity, 'getDuration')) && $entity->getDuration()
                    ? $entity->getDuration()
                    : $context['duration'] ?? Notification::DEFAULT_LONG_STANDING_DURATION;

                $query->andWhere(':timeDuration >= CAST(JSON_GET_TEXT(nt.additionalParams, :jsonPath) AS integer)')
                    ->setParameter('jsonPath', 'timeDuration', Types::STRING)
                    ->setParameter('timeDuration', $timeDuration);

                $notificationId = $context['notificationId'] ?? null;
                if ($notificationId) {
                    $query->andWhere('nt.id = :notificationId')->setParameter('notificationId', $notificationId);
                }
            }

            if ($event->isDistance()) {
                $distance = (method_exists($entity, 'getDistance')) && $entity->getDistance()
                    ? $entity->getDistance()
                    : null;

                if ($distance) {
                    $query->andWhere(':distance >= CAST(JSON_GET_TEXT(nt.additionalParams, :jsonPath) AS integer)')
                        ->setParameter('jsonPath', 'distance', Types::STRING)
                        ->setParameter('distance', $distance);
                }
                $notificationId = $context['notificationId'] ?? null;
                if ($notificationId) {
                    $query->andWhere('nt.id = :notificationId')->setParameter('notificationId', $notificationId);
                }
            }
        }

        if ($event->isSensorTemperature()) {
            $sensorTemperature = $entity->getTemperature();

            if (is_null($sensorTemperature)) {
                return null;
            }

            $prevTrackerHistorySensor = $this->getEntityManager()->getRepository(TrackerHistorySensor::class)
                ->getPrevTrackerHistorySensor($entity, true);
            $prevTemp = $prevTrackerHistorySensor ? $prevTrackerHistorySensor->getTemperature() : null;

            $query->andWhere(
                $query->expr()->orX(
                    $query->expr()->andX(
                        $query->expr()->gt(
                            $sensorTemperature,
                            'CAST(JSON_GET_TEXT(nt.additionalParams, :temperature) AS DECIMAL)'
                        ),
                        (!is_null($prevTemp) ? $query->expr()->lt(
                            $prevTemp,
                            'CAST(JSON_GET_TEXT(nt.additionalParams, :temperature) AS DECIMAL)'
                        ) : null),
                        $query->expr()->eq('CAST(JSON_GET_TEXT(nt.additionalParams, \'type\') AS TEXT)', ':greater')
                    ),
                    $query->expr()->andX(
                        $query->expr()->lt(
                            $sensorTemperature,
                            'CAST(JSON_GET_TEXT(nt.additionalParams, :temperature) AS DECIMAL)'
                        ),
                        (!is_null($prevTemp) ? $query->expr()->gt(
                            $prevTemp,
                            'CAST(JSON_GET_TEXT(nt.additionalParams, :temperature) AS DECIMAL)'
                        ) : null),
                        $query->expr()->eq('CAST(JSON_GET_TEXT(nt.additionalParams, \'type\') AS TEXT)', ':less')
                    ),
                    $query->expr()->andX(
                        $query->expr()->orX(
                            $query->expr()->lt(
                                $sensorTemperature,
                                'CAST(JSON_GET_TEXT(nt.additionalParams, \'from\') AS DECIMAL)'
                            ),
                            $query->expr()->gt(
                                $sensorTemperature,
                                'CAST(JSON_GET_TEXT(nt.additionalParams, \'to\') AS DECIMAL)'
                            )
                        ),
                        $query->expr()->eq('CAST(JSON_GET_TEXT(nt.additionalParams, \'type\') AS TEXT)', ':outside'),
                        (!is_null($prevTemp) ?
                            $query->expr()->andX(
                                $query->expr()->gt(
                                    $prevTemp,
                                    'CAST(JSON_GET_TEXT(nt.additionalParams, \'from\') AS DECIMAL)'
                                ),
                                $query->expr()->lt(
                                    $prevTemp,
                                    'CAST(JSON_GET_TEXT(nt.additionalParams, \'to\') AS DECIMAL)'
                                )
                            ) : null)
                    )
                )
            )
                ->setParameter('temperature', 'temperature')
                ->setParameter('less', Notification::SENSOR_TYPE_LESS)
                ->setParameter('greater', Notification::SENSOR_TYPE_GREATER)
                ->setParameter('outside', Notification::SENSOR_TYPE_OUTSIDE);
        }

        if ($event->isSensorHumidity()) {
            $sensorHumidity = $entity->getHumidity();

            if (is_null($sensorHumidity)) {
                return null;
            }

            $prevTrackerHistorySensor = $this->getEntityManager()->getRepository(TrackerHistorySensor::class)
                ->getPrevTrackerHistorySensor($entity, true);
            $prevHumidity = $prevTrackerHistorySensor ? $prevTrackerHistorySensor->getHumidity() : null;

            $query->andWhere(
                $query->expr()->orX(
                    $query->expr()->andX(
                        $query->expr()->gt(
                            $sensorHumidity,
                            'CAST(JSON_GET_TEXT(nt.additionalParams, :humidity) AS DECIMAL)'
                        ),
                        (!is_null($prevHumidity) ? $query->expr()->lt(
                            $prevHumidity,
                            'CAST(JSON_GET_TEXT(nt.additionalParams, :humidity) AS DECIMAL)'
                        ) : null),
                        $query->expr()->eq('CAST(JSON_GET_TEXT(nt.additionalParams, \'type\') AS TEXT)', ':greater')
                    ),
                    $query->expr()->andX(
                        $query->expr()->lt(
                            $sensorHumidity,
                            'CAST(JSON_GET_TEXT(nt.additionalParams, :humidity) AS DECIMAL)'
                        ),
                        (!is_null($prevHumidity) ? $query->expr()->gt(
                            $prevHumidity,
                            'CAST(JSON_GET_TEXT(nt.additionalParams, :humidity) AS DECIMAL)'
                        ) : null),
                        $query->expr()->eq('CAST(JSON_GET_TEXT(nt.additionalParams, \'type\') AS TEXT)', ':less')
                    ),
                    $query->expr()->andX(
                        $query->expr()->orX(
                            $query->expr()->lt(
                                $sensorHumidity,
                                'CAST(JSON_GET_TEXT(nt.additionalParams, \'from\') AS DECIMAL)'
                            ),
                            $query->expr()->gt(
                                $sensorHumidity,
                                'CAST(JSON_GET_TEXT(nt.additionalParams, \'to\') AS DECIMAL)'
                            )
                        ),
                        (!is_null($prevHumidity) ?
                            $query->expr()->andX(
                                $query->expr()->gt(
                                    $prevHumidity,
                                    'CAST(JSON_GET_TEXT(nt.additionalParams, \'from\') AS DECIMAL)'
                                ),
                                $query->expr()->lt(
                                    $prevHumidity,
                                    'CAST(JSON_GET_TEXT(nt.additionalParams, \'to\') AS DECIMAL)'
                                )
                            ) : null),
                        $query->expr()->eq('CAST(JSON_GET_TEXT(nt.additionalParams, \'type\') AS TEXT)', ':outside')
                    )
                )
            )
                ->setParameter('humidity', 'humidity')
                ->setParameter('less', Notification::SENSOR_TYPE_LESS)
                ->setParameter('greater', Notification::SENSOR_TYPE_GREATER)
                ->setParameter('outside', Notification::SENSOR_TYPE_OUTSIDE);
        }

        if ($event->isSensorLight()) {
            $sensorLight = $entity->getLight();

            if (is_null($sensorLight)) {
                return null;
            }

            $query->andWhere(':sensorLight = CAST(JSON_GET_TEXT(nt.additionalParams, :light) AS INTEGER)')
                ->setParameter('sensorLight', $sensorLight)
                ->setParameter(':light', 'light');
        }

        if ($event->isSensorBatteryLevel()) {
            $sensorBatteryLevel = $entity->getBatteryPercentage();

            if (is_null($sensorBatteryLevel)) {
                return null;
            }

            $prevTrackerHistorySensor = $this->getEntityManager()->getRepository(TrackerHistorySensor::class)
                ->getPrevTrackerHistorySensor($entity, true);
            $prevBatteryLevel = $prevTrackerHistorySensor ? $prevTrackerHistorySensor->getBatteryPercentage() : null;
            if (!is_null($prevBatteryLevel)) {
                $query->andWhere(
                    ':prevBatteryLevel > CAST(JSON_GET_TEXT(nt.additionalParams, :batteryLevel) AS DECIMAL)'
                )->setParameter('prevBatteryLevel', $prevBatteryLevel);
            }

            $query->andWhere(
                ':sensorBatteryLevel < CAST(JSON_GET_TEXT(nt.additionalParams, :batteryLevel) AS DECIMAL)'
            )
                ->setParameter('sensorBatteryLevel', $sensorBatteryLevel)
                ->setParameter('batteryLevel', 'batteryLevel');
        }

        if ($event->isSensorStatus()) {
            $sensorStatus = $entity->getDeviceSensor()->getStatus();

            $query->andWhere(
                'CAST(:sensorStatus AS TEXT) = CAST(JSON_GET_TEXT(nt.additionalParams, \'status\') AS TEXT)'
            )->setParameter('sensorStatus', $sensorStatus);
        }

        if ($event->isSensorIOStatus()) {
            /** @var TrackerHistoryIO $entity */
            $sensorIOStatus = $entity->getStatusIO();

            if (is_null($sensorIOStatus)) {
                return null;
            }

            $query->andWhere(
                'CAST(:sensorIOStatus AS TEXT) = CAST(JSON_GET_TEXT(nt.additionalParams, \'statusIO\') AS TEXT)'
            )
                ->setParameter('sensorIOStatus', $sensorIOStatus);
        }

        if ($event->isSensorIOType()) {
            /** @var TrackerHistoryIO $entity */
            $sensorIOType = $entity->getType() ?? null;

            if (!is_null($sensorIOType)) {
                $query->andWhere(
                    'CAST(:sensorIOTypeId AS TEXT) = CAST(JSON_GET_TEXT(nt.additionalParams, \'sensorIOTypeId\') AS TEXT)'
                )
                    ->setParameter('sensorIOTypeId', $sensorIOType->getId());
            }

        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param Event $event
     * @param Team|null $team
     * @param \DateTime $dt
     * @param string $param
     * @param string $order
     * @return |null
     */
    public function getTeamNotificationsParamValue(
        Event $event,
        ?Team $team,
        \DateTime $dt,
        string $param,
        $order = Criteria::ASC
    ) {
        //for searching ntf in team timezone
        if ($team && $event->isListenerTeam()) {
            $dt = $this->getDateTimeWithTimeZone($team, $dt);
        }

        $qb = $query = $this->getEntityManager()->createQueryBuilder();

        $query = $qb
            ->select('JSON_GET_TEXT(nt.additionalParams, :jsonPath) as value')
            ->from(Notification::class, 'nt')
            ->where('nt.event = :event')
            ->andWhere('nt.status = :status')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->andX(
                        $query->expr()->lte('nt.eventTrackingTimeFrom', 'nt.eventTrackingTimeUntil'),
                        $query->expr()->between(':eventTime', 'nt.eventTrackingTimeFrom', 'nt.eventTrackingTimeUntil'),
                    ),
                    $qb->expr()->andX(
                        $query->expr()->gte('nt.eventTrackingTimeFrom', 'nt.eventTrackingTimeUntil'),
                        $query->expr()->lte(':eventTime', 'nt.eventTrackingTimeFrom'),
                        $query->expr()->lte(':eventTime', 'nt.eventTrackingTimeUntil'),
                    ),
                    $qb->expr()->andX(
                        $query->expr()->gte('nt.eventTrackingTimeFrom', 'nt.eventTrackingTimeUntil'),
                        $query->expr()->gte(':eventTime', 'nt.eventTrackingTimeFrom'),
                    )
                )
            )
            ->andWhere('CAST(nt.eventTrackingDays AS text) LIKE :eventDay')
            ->setParameter('event', $event)
            ->setParameter('status', Notification::STATUS_ENABLED, Types::STRING)
            ->setParameter('eventTime', $dt->format('H:i'), Types::STRING)
            ->setParameter('eventDay', '%' . strtolower($dt->format('l')) . '%')
            ->orderBy('value', $order)
            ->setParameter('jsonPath', $param, Types::STRING);

        if ($team) {
            $query->andWhere('nt.listenerTeam = :team')->setParameter('team', $team);
        }

        $result = $query->getQuery()->getResult();

        return isset($result[0]) ? $result[0]['value'] : null;
    }

    /**
     * @param Notification $notification
     * @param array $recipients
     * @throws \Doctrine\ORM\ORMException
     */
    public function fillRecipients(Notification $notification, array $recipients)
    {
        $updated = [];

        foreach ($recipients as $dRecipient) {
            $existRecipient = $notification->getRecipients()->filter(
                static function (NotificationRecipients $recipient) use ($dRecipient) {
                    return $recipient->getType() === $dRecipient['type'];
                }
            )->first();

            /** Update or create new and add*/
            if (false === $existRecipient) {
                $notification->addRecipient(
                    $recipient = (new NotificationRecipients())
                        ->setType($dRecipient['type'])
                        ->setValue($dRecipient['value'])
                );
            } else {
                $recipient = $existRecipient
                    ->setType($dRecipient['type'])
                    ->setValue($dRecipient['value']);
            }
            $updated[] = $recipient;
            $this->getEntityManager()->persist($recipient);
        }

        /** remove */
        $notification->getRecipients()->map(
            function ($r) use ($updated) {
                $result = array_filter(
                    $updated,
                    static function ($u) use ($r) {
                        return $u === $r;
                    },
                    ARRAY_FILTER_USE_BOTH
                );

                if (0 === count($result)) {
                    $this->getEntityManager()->remove($r);
                }
            }
        );
    }

    public function fillAcknowledgeRecipients(Notification $notification, array $recipients)
    {
        $updated = [];

        foreach ($recipients as $dRecipient) {
            $existRecipient = $notification->getAcknowledgeRecipients()->filter(
                static function (AcknowledgeRecipients $recipient) use ($dRecipient) {
                    return $recipient->getType() === $dRecipient['type'];
                }
            )->first();

            /** Update or create new and add*/
            if (false === $existRecipient) {
                $notification->addAcknowledgeRecipient(
                    $recipient = (new AcknowledgeRecipients())
                        ->setType($dRecipient['type'])
                        ->setValue($dRecipient['value'] ?? [])
                );
            } else {
                $recipient = $existRecipient
                    ->setType($dRecipient['type'])
                    ->setValue($dRecipient['value'] ?? []);
            }
            $updated[] = $recipient;
            $this->getEntityManager()->persist($recipient);
        }

        /** remove */
        $notification->getAcknowledgeRecipients()->map(
            function ($r) use ($updated) {
                $result = array_filter(
                    $updated,
                    static function ($u) use ($r) {
                        return $u === $r;
                    },
                    ARRAY_FILTER_USE_BOTH
                );

                if (0 === count($result)) {
                    $this->getEntityManager()->remove($r);
                }
            }
        );
    }

    /**
     * @param Notification $notification
     * @param array $transports
     * @throws \Doctrine\ORM\ORMException
     */
    public function fillTransports(Notification $notification, array $transports)
    {
        $added = [];

        $settingsMap = Transport::getSettingsMap();

        $preparedTransports = [];

        foreach ($transports as $alias) {
            $preparedTransports = array_merge($preparedTransports, $settingsMap[$alias]);
        }

        foreach ($preparedTransports as $tAlias) {
            $existTransport = $notification->getTransports()->filter(
                static function (NotificationTransports $recipient) use ($tAlias) {
                    return $recipient->getTransport()->getAlias() === $tAlias;
                }
            )->first();

            if (false !== $existTransport) {
                $added[] = $existTransport;
                continue;
            }

            $notification->addTransport(
                $transport = (new NotificationTransports())
                    ->setTransport(
                        $this
                            ->getEntityManager()
                            ->getRepository(Transport::class)
                            ->findOneBy(['alias' => $tAlias])
                    )
            );
            $this->getEntityManager()->persist($transport);

            $added[] = $transport;
        }
        /** remove */
        $notification->getTransports()->map(
            function ($r) use ($added) {
                $result = array_filter(
                    $added,
                    static function ($u) use ($r) {
                        return $u === $r;
                    },
                    ARRAY_FILTER_USE_BOTH
                );

                if (0 === count($result)) {
                    $this->getEntityManager()->remove($r);
                }
            }
        );
    }


    /**
     * @param Notification $notification
     * @param array $scopes
     * @param string $category
     * @throws \Doctrine\ORM\ORMException
     */
    public function fillScope(Notification $notification, array $scopes, string $category)
    {
        $scope = $notification->getScopes()->filter(
            static function (NotificationScopes $notificationScope) use ($scopes, $category) {
                return $notificationScope->getType()->getCategory() === $category;
            }
        )->first();

        if (false === $scope) {
            $scope = new NotificationScopes();
            $notification->addScope($scope);
        }

        $scope
            ->setValue($scopes['value'])
            ->setType($notification->getEvent()->getScopeType($scopes['subtype'], $category));

        $this->getEntityManager()->persist($scope);
    }

    /**
     * @param Team $team
     * @param \DateTime $dt
     * @return \DateTime
     */
    public function getDateTimeWithTimeZone(Team $team, \DateTime $dt)
    {
        $dateTime = clone $dt;
        $timeZoneSetting = $team->getSettingsByName(Setting::TIMEZONE_SETTING);
        $timeZone = $timeZoneSetting
            ? $this->getEntityManager()->getRepository(TimeZone::class)->find($timeZoneSetting->getValue())
            : null;
        if ($timeZoneSetting && $timeZone) {
            $dateTime->setTimezone(new \DateTimeZone($timeZone->getName()));
        }

        return $dateTime;
    }

    /**
     * @param Team $team
     * @param Event|null $event
     * @param string|null $status
     * @return int|mixed|string
     */
    public function getNotificationsByTeam(Team $team, ?Event $event, string $status = null)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('nt')
            ->from(Notification::class, 'nt')
            ->where('nt.ownerTeam = :ownerTeam')
            ->orWhere('nt.ownerTeam IS NULL')
            ->setParameter('ownerTeam', $team)
            ->orderBy('nt.id');

        if ($event ?? null) {
            $query->andWhere('nt.event = :event')
                ->setParameter('event', $event);
        }

        if ($status ?? null) {
            $query->andWhere('nt.status = :status')
                ->setParameter('status', $status);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param Event $event
     * @param Team|null $team
     * @param \DateTime $dt
     * @return int|mixed|string
     */
    public function getNotificationsByListenerTeam(
        Event $event,
        ?Team $team,
        \DateTime $dt
    ) {
        //for searching ntf in team timezone
        if ($team) {
            $dt = $this->getDateTimeWithTimeZone($team, $dt);
        }

        $qb = $query = $this->getEntityManager()->createQueryBuilder();

        $query = $qb
            ->select('nt')
            ->from(Notification::class, 'nt')
            ->where('nt.event = :event')
            ->andWhere('nt.status = :status')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->andX(
                        $query->expr()->lte('nt.eventTrackingTimeFrom', 'nt.eventTrackingTimeUntil'),
                        $query->expr()->between(':eventTime', 'nt.eventTrackingTimeFrom', 'nt.eventTrackingTimeUntil'),
                    ),
                    $qb->expr()->andX(
                        $query->expr()->gte('nt.eventTrackingTimeFrom', 'nt.eventTrackingTimeUntil'),
                        $query->expr()->lte(':eventTime', 'nt.eventTrackingTimeFrom'),
                        $query->expr()->lte(':eventTime', 'nt.eventTrackingTimeUntil'),
                    ),
                    $qb->expr()->andX(
                        $query->expr()->gte('nt.eventTrackingTimeFrom', 'nt.eventTrackingTimeUntil'),
                        $query->expr()->gte(':eventTime', 'nt.eventTrackingTimeFrom'),
                    )
                )
            )
            ->andWhere('CAST(nt.eventTrackingDays AS text) LIKE :eventDay')
            ->andWhere('nt.listenerTeam = :team')
            ->setParameter('event', $event)
            ->setParameter('status', Notification::STATUS_ENABLED, Types::STRING)
            ->setParameter('eventTime', $dt->format('H:i'), Types::STRING)
            ->setParameter('eventDay', '%' . strtolower($dt->format('l')) . '%')
            ->setParameter('team', $team)
            ->orderBy('nt.createdAt');

        return $query->getQuery()->getResult();
    }

    public function addDeviceOverspeedingQuery($entity, $query, array $context = [])
    {
        if (in_array(ClassUtils::getClass($entity), [TrackerHistory::class])) {
            $speed = $entity->getSpeed() ?? Notification::DEFAULT_OVERSPEEDING;
        } elseif (in_array(ClassUtils::getClass($entity), [AreaHistory::class])) {
            $speed = $context['speed'] ?? Notification::DEFAULT_OVERSPEEDING;
        }

        $query->andWhere('CAST(JSON_GET_TEXT(nt.additionalParams, :jsonPath) AS DECIMAL) <= :speed')
            ->setParameter('jsonPath', 'overSpeed', Types::STRING)
            ->setParameter('speed', $speed ?? Notification::DEFAULT_OVERSPEEDING);

        return $query;
    }

    /**
     * @param $entity
     * @param $query
     * @return mixed
     */
    private function addDeviceTimeDurationWithDistanceQuery($entity, $query, array $context = [])
    {
        $timeDuration = ((method_exists($entity, 'getDuration')) && $entity->getDuration())
            ? $entity->getDuration()
            : $context['duration'] ?? null;

        $distance = (method_exists($entity, 'getDistance')) && $entity->getDistance()
            ? $entity->getDistance()
            : $context['distance'] ?? null;

        $query->andWhere(
            $query->expr()->orX(
                $query->expr()->andX(
                    $query->expr()->gte(
                        ':timeDuration',
                        'CAST(JSON_GET_TEXT(nt.additionalParams, :searchTime) AS DECIMAL)'
                    ),
                    $query->expr()->gte(
                        ':distance',
                        'CAST(JSON_GET_TEXT(nt.additionalParams, :searchDistance) AS DECIMAL)'
                    ),
                    $query->expr()->eq(
                        'CAST(JSON_GET_TEXT(nt.additionalParams, :exprOperator) AS TEXT)',
                        ':exprAnd'
                    ),
                ),
                $query->expr()->andX(
                    $query->expr()->orX(
                        (!is_null($timeDuration) ? $query->expr()->gte(
                            ':timeDuration',
                            'CAST(JSON_GET_TEXT(nt.additionalParams, :searchTime) AS DECIMAL)'
                        ) : null),
                        (!is_null($distance) ? $query->expr()->gte(
                            ':distance',
                            'CAST(JSON_GET_TEXT(nt.additionalParams, :searchDistance) AS DECIMAL)'
                        ) : null),
                    ),
                    $query->expr()->eq(
                        'CAST(JSON_GET_TEXT(nt.additionalParams, :exprOperator) AS TEXT)',
                        ':exprOr'
                    ),
                ),
                $query->expr()->andX(
                    $query->expr()->gte(
                        ':timeDuration',
                        'CAST(JSON_GET_TEXT(nt.additionalParams, :searchTime) AS DECIMAL)'
                    ),
                    $query->expr()->isNull(
                        'CAST(JSON_GET_TEXT(nt.additionalParams, :exprOperator) AS TEXT)',
                    ),
                ),
                $query->expr()->andX(
                    $query->expr()->gte(
                        ':distance',
                        'CAST(JSON_GET_TEXT(nt.additionalParams, :searchDistance) AS DECIMAL)'
                    ),
                    $query->expr()->isNull(
                        'CAST(JSON_GET_TEXT(nt.additionalParams, :exprOperator) AS TEXT)',
                    ),
                ),
            )
        )
            ->setParameter('timeDuration', $timeDuration)
            ->setParameter('distance', $distance)
            ->setParameter('exprOperator', Notification::EXPRESSION_OPERATOR)
            ->setParameter('exprAnd', Notification::OPERATOR_AND)
            ->setParameter('exprOr', Notification::OPERATOR_OR)
            ->setParameter('searchTime', Notification::TIME_DURATION)
            ->setParameter('searchDistance', Notification::DISTANCE);

        return $query;
    }

    public function addDeviceBatteryQuery(QueryBuilder $query, $entity): QueryBuilder
    {
        $deviceBattery = method_exists($entity, 'getBatteryVoltagePercentage')
            ? $entity->getBatteryVoltagePercentage()
            : null;

        if (!is_null($deviceBattery)) {
            $query->andWhere('CAST(JSON_GET_TEXT(nt.additionalParams, :jsonPath) AS DECIMAL) >= :deviceBattery')
                ->setParameter('jsonPath', Notification::DEVICE_BATTERY_PERCENTAGE, Types::STRING)
                ->setParameter('deviceBattery', $deviceBattery);

            $lastTh = $this->getEntityManager()->getRepository(TrackerHistory::class)
                ->getLastTrackerHistoryWithBattery($entity->getDevice(), $entity);
            $prevBattery = $lastTh ? $lastTh->getBatteryVoltagePercentage() : null;

            if (!is_null($prevBattery)) {
                $query->andWhere('CAST(JSON_GET_TEXT(nt.additionalParams, :jsonPath) AS DECIMAL) < :prevBattery')
                    ->setParameter('prevBattery', $prevBattery);
            }
        } else {
            return $query;
        }

        return $query;
    }

    public function getNtfCountByTeamAndEvent(Team $team, Event $event)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('COUNT(nt) as c')
            ->from(Notification::class, 'nt')
            ->where('nt.event = :event')
            ->andWhere('nt.status = :status')
            ->andWhere('nt.listenerTeam = :team')
            ->setParameter('event', $event)
            ->setParameter('status', BaseEntity::STATUS_ENABLED, Types::STRING)
            ->setParameter('team', $team);

        return $query->getQuery()->getSingleScalarResult();
    }
}
