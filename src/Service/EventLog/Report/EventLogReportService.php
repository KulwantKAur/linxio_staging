<?php

namespace App\Service\EventLog\Report;

use App\Entity\Client;
use App\Entity\Document;
use App\Entity\EventLog\EventLog;
use App\Entity\Invoice;
use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use App\Entity\Notification\Template;
use App\Entity\Reseller;
use App\Entity\Team;
use App\Entity\Tracker\TrackerIOType;
use App\Entity\Sensor;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Service\BaseService;
use App\Service\ElasticSearch\ElasticSearch;
use App\Service\EventLog\Factory\ReportEntityHandlerFactory;
use App\Service\EventLog\Interfaces\ReportBuilderInterface;
use App\Service\EventLog\Report\ReportBuilder\ReportBuilder;
use App\Service\Sensor\SensorService;
use App\Util\DateHelper;
use App\Util\MetricHelper;
use App\Util\StringHelper;
use Carbon\Carbon;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventLogReportService extends BaseService
{
    private EntityManager $em;
//    private ElasticSearch $evenLogFinder;
    protected TranslatorInterface $translator;
    protected ReportEntityHandlerFactory $entityHandlerFactory;

    public const ELASTIC_NESTED_FIELDS = [];
    public const ELASTIC_SIMPLE_FIELDS = [
        'id' => 'id',
        'teamId' => 'teamId',
        'eventId' => 'event.id',
        'importanceId' => 'event.importance.id',
        'triggeredBy' => 'triggeredBy',
        'triggeredDetails' => 'triggeredDetails',
        'eventSourceType' => 'eventSourceType',
        'eventSource' => 'eventSource',
        'eventTeam' => 'eventTeam',
        'notificationGenerated' => 'notificationGenerated',
        'notificationsList' => 'notificationsList',
    ];
    public const ELASTIC_RANGE_FIELDS = [
        'createdAt' => 'createdAt',
        'eventDate' => 'eventDate',
        'formattedDate' => 'formattedDate',
    ];

    public const TYPE_ADDITIONAL_INFO = 'additional_info';
    public const TYPE_BASIC_INFO = 'basic_info';
    public const TYPE_SENSOR_ADDITIONAL_INFO = 'sensor_additional_info';

    public const EVENT_SOURCE = 'eventSource';
    public const EVENT_SOURCE_TEAM = 'entityTeam';
    public const EVENT_SOURCE_NAME = 'name';
    public const EVENT_SOURCE_TYPE = 'type';

    /**
     * EventLogReportService constructor.
     * @param EntityManager $em
     * //     * @param TransformedFinder $evenLogFinder
     * @param TranslatorInterface $translator
     * @param ReportEntityHandlerFactory $entityHandlerFactory
     */
    public function __construct(
        EntityManager $em,
//        TransformedFinder $evenLogFinder,
        TranslatorInterface $translator,
        ReportEntityHandlerFactory $entityHandlerFactory
    ) {
        $this->em = $em;
//        $this->evenLogFinder = new ElasticSearch($evenLogFinder);
        $this->translator = $translator;
        $this->entityHandlerFactory = $entityHandlerFactory;
    }

    /**
     * @param array $params
     * @param User $user
     * @param bool $paginated
     * @return array
     * @throws \Elastica\Exception\ElasticsearchException
     */
    public function getEventLog(array $params, User $user, bool $paginated = true)
    {
        /** @var User $user */
        if ($user->isInClientTeam()) {
            $params['teamId'] = $user->getTeam()->getId();
        }
        if ($user->isInResellerTeam()) {
            $params['teamId'] = $this->em->getRepository(Reseller::class)->getResellerClientTeams($user->getReseller());
            $params['teamId'][] = $user->getTeamId();
        }

        if ($user->isInAdminTeam() && !$user->isSuperAdmin() && !$user->isAdmin()) {
            $params['teamId'] = $this->em->getRepository(Client::class)->getAdminClientTeams();
            $params['teamId'][] = $user->getTeamId();
        }

        $params['fields'] = array_merge(EventLog::DISPLAYED_VALUES, $params['fields'] ?? []);

        if (isset($params['eventId'])) {
            /** @var Event $event */
            $event = $this->em->getRepository(Event::class)->findOneBy(['id' => $params['eventId']]);
        }
        $fields = $this->prepareElasticFields($params);
        $result = $this->evenLogFinder->find($fields, $fields['_source'] ?? [], $paginated);

        return $this->prepareEventLogData($result, $user, $event ?? null);
    }

    public function getEventLogExport(array $params, User $user, bool $paginated = true)
    {
        // TODO - temporary refactoring
        $eventLogData = $this->getEventLog($params, $user, $paginated);

        $event = null;
        if (isset($params['eventId'])) {
            /** @var Event $event */
            $event = $this->em->getRepository(Event::class)->findOneBy(['id' => $params['eventId']]);
        }

        $teamNotificationByEvent = $this->em->getRepository(Notification::class)
            ->getNotificationsByTeam($user->getTeam(), $event);

        // TODO - change the forwarding of branch for drawing the event log
        $digitalIOTypes = $this->em->getRepository(TrackerIOType::class)->findAll();

        $handlerByEvent = $this->entityHandlerFactory->getInstance(
            $event,
            $user,
            $teamNotificationByEvent,
            $digitalIOTypes
        );

        return (new ReportBuilder($handlerByEvent))
            ->build($eventLogData, $user, $params['fields']);
    }

    /**
     * @param array $data
     * @param User $user
     * @param Event|null $event
     * @return array
     */
    public function prepareEventLogData(array $data, User $user, ?Event $event = null)
    {
        $allNotificationsByTeam = $this->em->getRepository(Notification::class)
            ->getNotificationsByTeam($user->getTeam(), $event);
        $IOTypes = $this->em->getRepository(TrackerIOType::class)->findAll();

        $data['data'] = !empty($data['data']) ? $data['data'] : [];

        $data['data'] = array_map(
            function ($eventLog) use ($allNotificationsByTeam, $IOTypes) {
                $eventEntity = ClassUtils::getRealClass($eventLog['event']['entity']);
                $eventName = $eventLog['event']['name'];
                $eventDetails = $eventLog['details'];
                $notificationsByEvent = !empty($eventLog[EventLog::NTF_LIST])
                    ? $this->getNotifications($eventLog[EventLog::NTF_LIST], $allNotificationsByTeam)
                    : null;
                $eventLog[self::EVENT_SOURCE] = $this->prepareEventSourceFields($eventLog);
                $eventTeam = $eventLog[self::EVENT_SOURCE][self::EVENT_SOURCE_TEAM]
                    ? [self::EVENT_SOURCE_TEAM => $eventLog[self::EVENT_SOURCE][self::EVENT_SOURCE_TEAM]]
                    : [];
                unset($eventLog['details']);

                switch ($eventEntity) {
                    case Event::ENTITY_TYPE_TRACKER_HISTORY:
                        $eventLog[EventLog::SHORT_DETAILS] = [
                            EventLog::LAST_COORDINATES => $eventDetails[EventLog::LAST_COORDINATES]
                                ?? ($eventDetails['context'][EventLog::LAST_COORDINATES] ?? null),
                            EventLog::DEVICE_IMEI => $eventDetails['device']['imei'] ?? null,
                            EventLog::VEHICLE_REG_NO =>
                                $eventDetails['device']['deviceInstallation']['vehicle']['regNo'] ?? null,
                            EventLog::ADDRESS => $eventDetails['context'][EventLog::ADDRESS]
                                ?? ($eventDetails['device']['trackerData'][EventLog::ADDRESS] ?? null),
                            EventLog::AREAS =>
                                $eventDetails['device']['deviceInstallation']['vehicle'][EventLog::AREAS] ?? null,
                            EventLog::LIMIT => !empty($notificationsByEvent)
                                ? $this->getNotificationsInfo(
                                    $notificationsByEvent,
                                    [Notification::ADDITIONAL_PARAMS],
                                    $eventTeam,
                                    self::TYPE_ADDITIONAL_INFO
                                )
                                : null
                        ];

                        switch ($eventName) {
                            case Event::TRACKER_VOLTAGE:
                                $addData = [
                                    EventLog::DEVICE_VOLTAGE => $eventDetails['externalVoltage']
                                        ? $eventDetails['externalVoltage'] / 1000
                                        : null,
                                ];
                                $eventLog[EventLog::SHORT_DETAILS][EventLog::LIMIT] =
                                    [[$eventLog[EventLog::SHORT_DETAILS][EventLog::LIMIT][0][EventLog::DEVICE_VOLTAGE] ?? null]];
                                $eventLog[EventLog::SHORT_DETAILS] = array_merge(
                                    $eventLog[EventLog::SHORT_DETAILS],
                                    $addData
                                );
                                break;
                            case Event::VEHICLE_OVERSPEEDING:
                                $addData = [
                                    EventLog::MAX_SPEED =>
                                        MetricHelper::speedToHumanKmH($eventDetails[EventLog::SPEED] ?? null)
                                        ?? MetricHelper::speedToHumanKmH(
                                            $eventDetails['context'][EventLog::SPEED] ?? null
                                        ),
                                    EventLog::DURATION => $eventDetails['context'][EventLog::DURATION] ?? null,
                                    EventLog::DISTANCE =>
                                        MetricHelper::metersToHumanKm(
                                            $eventDetails['context'][EventLog::DISTANCE] ?? null
                                        ),
                                ];

                                $eventLog[EventLog::SHORT_DETAILS] = array_merge(
                                    $eventLog[EventLog::SHORT_DETAILS],
                                    $addData
                                );
                                break;
                            case Event::EXCEEDING_SPEED_LIMIT:
                                $addData = [
                                    EventLog::SPEED_LIMIT =>
                                        MetricHelper::speedToHumanKmH($eventDetails[EventLog::SPEED_LIMIT] ?? null)
                                        ?? MetricHelper::speedToHumanKmH(
                                            $eventDetails['context'][EventLog::SPEED_LIMIT] ?? null
                                        ),
                                    EventLog::MAX_SPEED =>
                                        MetricHelper::speedToHumanKmH($eventDetails[EventLog::SPEED] ?? null)
                                        ?? MetricHelper::speedToHumanKmH(
                                            $eventDetails['context'][EventLog::SPEED] ?? null
                                        ),
                                    EventLog::SPEED_OVER_LIMIT_PERCENT =>
                                        ($eventDetails[EventLog::SPEED] ?? null) && ($eventDetails['context'][EventLog::SPEED_LIMIT] ?? null)
                                            ? round((1 - $eventDetails['context'][EventLog::SPEED_LIMIT] / $eventDetails[EventLog::SPEED]) * 100) : null,
                                    EventLog::DURATION => $eventDetails['context'][EventLog::DURATION] ?? null,
                                    EventLog::VEHICLE_DEFAULT_LABEL =>
                                        $eventDetails['device']['deviceInstallation']['vehicle']['defaultLabel'] ?? null,
                                    EventLog::DISTANCE =>
                                        MetricHelper::metersToHumanKm(
                                            $eventDetails['context'][EventLog::DISTANCE] ?? null
                                        ),
                                ];

                                $eventLog[EventLog::SHORT_DETAILS] = array_merge(
                                    $eventLog[EventLog::SHORT_DETAILS],
                                    $addData
                                );
                                break;
                            case Event::TRACKER_BATTERY_PERCENTAGE:
                                $addData = [
                                    EventLog::DEVICE_BATTERY_PERCENTAGE =>
                                        $eventDetails['batteryVoltagePercentage'] ?? null,
                                ];
                                $eventLog[EventLog::SHORT_DETAILS] = array_merge(
                                    $eventLog[EventLog::SHORT_DETAILS],
                                    $addData
                                );
                                break;
                            case Event::VEHICLE_LONG_STANDING:
                            case Event::VEHICLE_LONG_DRIVING:
                            case Event::VEHICLE_MOVING:
                                $addData = [
                                    EventLog::DURATION => $eventDetails['context'][EventLog::DURATION] ?? null,
                                    EventLog::DISTANCE =>
                                        MetricHelper::metersToHumanKm(
                                            $eventDetails['context'][EventLog::DISTANCE] ?? null
                                        ),
                                ];
                                $eventLog[EventLog::SHORT_DETAILS] = array_merge(
                                    $eventLog[EventLog::SHORT_DETAILS],
                                    $addData
                                );
                                break;
                        }

                        break;
                    case Event::ENTITY_TYPE_IDLING:
                        $eventLog[EventLog::SHORT_DETAILS] = [
                            EventLog::LAST_COORDINATES =>
                                $eventDetails['pointFinish'][EventLog::LAST_COORDINATES] ?? null,
                            EventLog::DEVICE_IMEI => $eventDetails['device']['imei'] ?? null,
                            EventLog::VEHICLE_REG_NO => $eventDetails['vehicle']['regNo'] ?? null,
                            EventLog::ADDRESS => $eventDetails[EventLog::ADDRESS] ?? null,
                            EventLog::AREAS => $eventDetails['vehicle'][EventLog::AREAS] ?? null,
                            EventLog::LIMIT => !empty($notificationsByEvent)
                                ? $this->getNotificationsInfo(
                                    $notificationsByEvent,
                                    [Notification::ADDITIONAL_PARAMS],
                                    $eventTeam,
                                    self::TYPE_ADDITIONAL_INFO
                                )
                                : null,
                        ];
                        $eventLog[EventLog::TRIGGERED_DETAILS] = $eventLog[EventLog::TRIGGERED_DETAILS] === 'system'
                            ? '-' : $eventLog[EventLog::TRIGGERED_DETAILS];
                        break;
                    case Event::ENTITY_TYPE_AREA_HISTORY:
                        $eventLog[EventLog::SHORT_DETAILS] = [
                            EventLog::LAST_COORDINATES =>
                                $eventDetails['context'][EventLog::LAST_COORDINATES]
                                ?? ($eventDetails['device']['trackerData'][EventLog::LAST_COORDINATES] ?? null),
                            EventLog::DEVICE_IMEI => $eventDetails['device']['imei'] ?? null,
                            EventLog::VEHICLE_REG_NO => $eventDetails['vehicle']['regNo'] ?? null,
                            EventLog::ADDRESS => $eventDetails['context'][EventLog::ADDRESS]
                                ?? ($eventDetails['device']['trackerData'][EventLog::ADDRESS] ?? null),
                            EventLog::AREAS => $eventDetails['context'][EventLog::AREAS] ?? $eventDetails['vehicle'][EventLog::AREAS] ?? null,
                            EventLog::MAX_SPEED => MetricHelper::speedToHumanKmH(
                                $eventDetails['context'][EventLog::SPEED] ?? null
                            ),
                            EventLog::LIMIT => !empty($notificationsByEvent)
                                ? $this->getNotificationsInfo(
                                    $notificationsByEvent,
                                    [Notification::ADDITIONAL_PARAMS],
                                    $eventTeam,
                                    self::TYPE_ADDITIONAL_INFO
                                )
                                : null,
                        ];
                        break;
                    case Event::ENTITY_TYPE_TRACKER_HISTORY_IO:
                        $eventLog[EventLog::SHORT_DETAILS] = [
                            EventLog::LAST_COORDINATES =>
                                $eventDetails['device']['trackerData'][EventLog::LAST_COORDINATES] ?? null,
                            EventLog::DEVICE_IMEI => $eventDetails['device']['imei'] ?? null,
                            EventLog::VEHICLE_REG_NO =>
                                $eventDetails['device']['deviceInstallation']['vehicle']['regNo'] ?? null,
                            EventLog::ADDRESS => $eventDetails['device']['trackerData'][EventLog::ADDRESS] ?? null,
                            EventLog::AREAS =>
                                $eventDetails['device']['deviceInstallation']['vehicle'][EventLog::AREAS] ?? null,
                            EventLog::SENSOR_STATUS => isset($eventDetails['statusIO'])
                                ? (($eventDetails['statusIO']) ? 'online' : 'offline')
                                : null,
                            EventLog::SENSOR_IO_TYPE => !empty($eventDetails['sensorIOTypeId'])
                                ? $this->getNameIOType($IOTypes, $eventDetails['sensorIOTypeId'])
                                : null,
                        ];
                        $eventLog[EventLog::TRIGGERED_DETAILS] = $eventLog[EventLog::TRIGGERED_DETAILS] === 'system'
                            ? '-' : $eventLog[EventLog::TRIGGERED_DETAILS];
                        break;
                        break;
                    case Event::ENTITY_TYPE_DEVICE:
                        $eventLog[EventLog::SHORT_DETAILS] = [
                            EventLog::DEVICE_ID => $eventDetails['id'] ?? null,
                            EventLog::DEVICE_IMEI => $eventDetails['imei'] ?? null,
                        ];
                        break;
                    case Event::ENTITY_TYPE_ROUTE:
                        $eventLog[EventLog::SHORT_DETAILS] = [
                            EventLog::LAST_COORDINATES =>
                                $eventDetails['pointFinish'][EventLog::LAST_COORDINATES] ?? null,
                            EventLog::DEVICE_IMEI => $eventDetails['device']['imei'] ?? null,
                            EventLog::VEHICLE_REG_NO => $eventDetails['vehicle']['regNo'] ?? null,
                            EventLog::ADDRESS => $eventDetails[EventLog::ADDRESS] ?? null,
                            EventLog::DURATION => $eventDetails[EventLog::DURATION] ?? null,
                            EventLog::DISTANCE => MetricHelper::metersToHumanKm(
                                $eventDetails[EventLog::DISTANCE] ?? null
                            ),
                            EventLog::LIMIT => !empty($notificationsByEvent)
                                ? $this->getNotificationsInfo(
                                    $notificationsByEvent,
                                    [Notification::ADDITIONAL_PARAMS],
                                    $eventTeam,
                                    self::TYPE_ADDITIONAL_INFO
                                )
                                : null,
                        ];

                        switch ($eventName) {
                            case Event::DIGITAL_FORM_IS_NOT_COMPLETED:
                                $addData = [
                                    EventLog::FORM => $eventDetails['context'][EventLog::FORM]
                                        ? implode(",", $eventDetails['context'][EventLog::FORM])
                                        : null,
                                ];
                                $eventLog[EventLog::SHORT_DETAILS] = array_merge(
                                    $eventLog[EventLog::SHORT_DETAILS],
                                    $addData
                                );
                                break;
                        }
                        break;
                    case Event::ENTITY_TYPE_DOCUMENT_RECORD:
                        $eventLog[EventLog::SHORT_DETAILS] = [
                            EventLog::EXPIRED_DATE => $eventDetails['expDate'] ?? null,
                            'title' => $eventDetails['document']['title'] ?? null,
                        ];
                        break;
                    case Event::ENTITY_TYPE_REMINDER:
                        $eventLog[EventLog::SHORT_DETAILS] = [
                            EventLog::EXPIRED_DATE => $eventDetails['controlDate'] ?? null,
                            EventLog::VEHICLE_REG_NO => $eventDetails['vehicle']['regNo'] ?? null,
                        ];
                        break;
                    case Event::ENTITY_TYPE_SERVICE_RECORD:
                        switch ($eventName) {
                            case Event::SERVICE_RECORD_ADDED:
                                $eventLog[EventLog::SHORT_DETAILS] = [
                                    EventLog::VEHICLE_REG_NO => $eventDetails['vehicleRegNo'] ?? null,
                                ];
                                break;
                            case Event::SERVICE_REPAIR_ADDED:
                                $eventLog[EventLog::SHORT_DETAILS] = [
                                    EventLog::VEHICLE_REG_NO => $eventDetails['repairVehicle']['regNo'] ?? null,
                                ];
                                break;
                        }
                        break;
                    case Event::ENTITY_TYPE_VEHICLE_ODOMETER:
                        $eventLog[EventLog::SHORT_DETAILS] = [
                            EventLog::OLD_VALUE => !empty($eventDetails['context']['oldValue'])
                                ? (int)($eventDetails['context']['oldValue'] / 1000)
                                : null,
                            EventLog::NEW_VALUE => !empty($eventDetails['odometer'])
                                ? (int)($eventDetails['odometer'] / 1000)
                                : null,
                        ];
                        break;
                    case Event::ENTITY_TYPE_VEHICLE:
                    case Event::ENTITY_TYPE_USER:
                        $eventLog[EventLog::SHORT_DETAILS] = [
                            EventLog::OLD_VALUE => $eventDetails['context']['oldValue'] ?? null,
                        ];
                        switch ($eventName) {
                            case Event::VEHICLE_REASSIGNED:
                                $fullName = !empty($eventDetails['driver'])
                                    ? ($eventDetails['driver']['name'] ?? null)
                                    . ' ' . ($eventDetails['driver']['surname'] ?? null)
                                    : null;
                                $addData = [
                                    EventLog::NEW_VALUE => $fullName
                                ];
                                $eventLog[EventLog::SHORT_DETAILS] = array_merge(
                                    $eventLog[EventLog::SHORT_DETAILS],
                                    $addData
                                );
                                break;
                            case Event::ADMIN_USER_CREATED:
                            case Event::ADMIN_USER_BLOCKED:
                            case Event::ADMIN_USER_DELETED:
                            case Event::ADMIN_USER_PWD_RESET:
                            case Event::ADMIN_USER_CHANGED_NAME:
                            case Event::LOGIN_AS_USER:
                                if ($eventDetails['team']['type'] === Team::TEAM_RESELLER) {
                                    $eventLog[EventLog::EVENT_TEAM] = $eventDetails['team']['resellerName']
                                        ?? $eventLog[EventLog::EVENT_TEAM] ?? null;
                                }
                                break;
                            case Event::VEHICLE_OFFLINE:
                                $eventLog[EventLog::SHORT_DETAILS] = [
                                    EventLog::LAST_COORDINATES => $eventDetails['context']['lastCoordinates'] ?? null,
                                    EventLog::ADDRESS => $eventDetails['context']['address'] ?? null,
                                    'approximateDuration' => is_null($eventDetails['context'][EventLog::DURATION]) &&
                                        isset($eventDetails['context']['lastCoordinates']['ts']) && isset($eventDetails['context']['gpsStatusDurationSetting']),
                                    EventLog::DURATION => $eventDetails['context'][EventLog::DURATION] ??
                                        (isset($eventDetails['context']['lastCoordinates']['ts']) && isset($eventDetails['context']['gpsStatusDurationSetting'])
                                            ? (new Carbon())->diffInSeconds($eventDetails['context']['lastCoordinates']['ts']) : null),
                                    EventLog::DEVICE_IMEI => $eventDetails['device']['imei'] ?? null,
                                    EventLog::VEHICLE_REG_NO => $eventDetails['regNo'] ?? null,
                                    EventLog::AREAS => $eventDetails[EventLog::AREAS] ?? null,
                                ];
                                break;
                        }
                        break;
                    case Event::ENTITY_TYPE_DIGITAL_FORM_ANSWER:
                        $eventLog[EventLog::SHORT_DETAILS] = [
                            EventLog::VEHICLE_REG_NO => $eventDetails['vehicle']['regNo'] ?? null,
                            EventLog::USER => $eventDetails['user']['email'] ?? null,
                        ];
                        break;
                    case Event::ENTITY_TYPE_TRACKER_HISTORY_SENSOR:
                        $eventLog[EventLog::SHORT_DETAILS] = [
                            EventLog::SENSOR_TEMPERATURE => $eventDetails['temperature'] ?? null,
                            EventLog::SENSOR_HUMIDITY => $eventDetails['humidity'] ?? null,
                            EventLog::SENSOR_LIGHT => isset($eventDetails['light'])
                                ? ($eventDetails['light'] ? Sensor::LIGHT_ON : Sensor::LIGHT_OFF)
                                : null,
                            EventLog::SENSOR_BATTERY_LEVEL => $eventDetails['batteryPercentage'] ?? null,
                            EventLog::SENSOR_STATUS => $eventDetails['deviceSensorStatus'] ?? null,
                            EventLog::USER => $eventDetails['user']['email'] ?? null,
                            EventLog::LIMIT => !empty($notificationsByEvent)
                                ? $this->getNotificationsInfo(
                                    $notificationsByEvent,
                                    [Notification::ADDITIONAL_PARAMS],
                                    $eventTeam,
                                    self::TYPE_SENSOR_ADDITIONAL_INFO
                                )
                                : null,
                        ];
                        break;
                    case Event::ENTITY_TYPE_INVOICE:
                        switch ($eventName) {
                            case Event::STRIPE_INTEGRATION_ERROR:
                            case Event::XERO_INTEGRATION_ERROR:
                            case Event::XERO_INVOICE_CREATION_ERROR:
                            case Event::PAYMENT_FAILED:
                            case Event::STRIPE_PAYMENT_FAILED:
                            case Event::XERO_PAYMENT_CREATION_ERROR:
                                $eventLog[EventLog::SHORT_DETAILS] = [
                                    EventLog::ERROR_MSG => ($eventDetails[EventLog::CONTEXT]['message'] ?? '')
                                        . ' ' . (implode(' ', $eventDetails[EventLog::CONTEXT]['errors'] ?? [])),
                                    EventLog::ERRORS => $eventDetails[EventLog::CONTEXT]['errors'] ?? null,
                                ];
                                break;
                            case Event::XERO_INVOICE_CREATED:
                            case Event::XERO_PAYMENT_CREATED:
                                $eventLog[EventLog::SHORT_DETAILS] = [
                                    EventLog::EXT_INVOICE_ID => $eventDetails[EventLog::EXT_INVOICE_ID] ?? null,
                                ];
                                break;
                            default:
                                break;
                        }
                        break;
                    case Event::ENTITY_TYPE_TEAM:
                        $eventLog[EventLog::SHORT_DETAILS] = [
                            EventLog::ERROR_MSG => $eventDetails[EventLog::CONTEXT]['message'] ?? null,
                        ];
                        break;
                    default:
                        break;
                }

                $eventLog[EventLog::NTF_LIST] = !empty($notificationsByEvent)
                    ? $this->getNotificationsInfo(
                        $notificationsByEvent,
                        ['id', 'title', 'eventId'],
                        $eventTeam
                    ) : null;

                return $eventLog;
            },
            $data['data']
        );

        return $data;
    }

    /**
     * @param array $ids
     * @param array $notificationsByEvent
     * @return array|array[]
     */
    public function getNotifications(array $ids, array $notificationsByEvent)
    {
        return array_values(
            array_filter(
                array_map(
                    function ($ids) use ($notificationsByEvent) {
                        return \array_filter(
                            $notificationsByEvent,
                            static function (Notification $notification) use ($ids) {
                                return $notification->getId() === $ids;
                            }
                        );
                    },
                    $ids
                )
            )
        );
    }

    /**
     * @param array $notifications
     * @param array $searchFields
     * @param array $eventTeam
     * @param string $type
     * @return array|array[]
     */
    public function getNotificationsInfo(
        array $notifications,
        array $searchFields,
        array $eventTeam,
        string $type = self::TYPE_BASIC_INFO
    ) {
        return array_map(
            function ($notifications) use ($searchFields, $type, $eventTeam) {
                $notifications = \array_map(
                    static function (Notification $notification) use ($searchFields, $eventTeam) {
                        return array_merge($notification->toArray($searchFields), $eventTeam);
                    },
                    $notifications
                );

                switch ($type) {
                    case self::TYPE_BASIC_INFO:
                        return array_shift($notifications);
                    case self::TYPE_ADDITIONAL_INFO:
                        $data = array_shift($notifications)[Notification::ADDITIONAL_PARAMS];

                        foreach ($data as $key => $value) {
                            switch ($key) {
                                case Notification::OVER_SPEED:
                                    $data[$key] = MetricHelper::speedToHumanKmH($value);
                                    break;
                                case Notification::DISTANCE:
                                    $data[$key] = MetricHelper::metersToHumanKm($value);
                                    break;
                                case Notification::TIME_DURATION:
                                    $data[$key] = DateHelper::seconds2human($value);
                                    break;
                                case Notification::EXPRESSION_OPERATOR:
                                    if ($data[Notification::EXPRESSION_OPERATOR] === Notification::OPERATOR_AND) {
                                        $data[$key] = '+';
                                    } else {
                                        $data[$key] = '/';
                                    }
                                    break;
                                case Notification::AREA_TRIGGER_TYPE:
                                    if ($key === Notification::AREA_TRIGGER_TYPE_INSIDE) {
                                        $data[$key] = $this->translator
                                            ->trans('inside_areas', [], Template::TRANSLATE_DOMAIN);
                                    }

                                    if ($key === Notification::AREA_TRIGGER_TYPE_OUTSIDE) {
                                        $data[$key] = $this->translator
                                            ->trans('outside_areas', [], Template::TRANSLATE_DOMAIN);
                                    }
                                    break;
                                default:
                                    $data[$key] = $value;
                                    break;
                            }
                        }
                        return $this->prepareData($data);
                    case self::TYPE_SENSOR_ADDITIONAL_INFO:
                        $data = array_shift($notifications)[Notification::ADDITIONAL_PARAMS];

                        return SensorService::getEventLogLimit($data);
                    default:
                        return $notifications ?? null;
                }
            },
            $notifications
        );
    }

    /**
     * @param $data
     * @return array
     */
    public function prepareData($data): array
    {
        $limit = null;
        $timeDuration = $data[Notification::TIME_DURATION] ?? null;
        $distance = $data[Notification::DISTANCE] ?? null;
        $exprOperator = $data[Notification::EXPRESSION_OPERATOR] ?? null;
        $overSpeed = $data[Notification::OVER_SPEED] ?? null;
        $areaTriggerType = $data[Notification::AREA_TRIGGER_TYPE] ?? null;

        if ($timeDuration && $exprOperator && $distance) {
            $limit = vsprintf('%s %s %s', [$timeDuration, $exprOperator, $distance]);
        } elseif ($timeDuration) {
            $limit = vsprintf('%s', [$timeDuration]);
        } elseif ($distance) {
            $limit = vsprintf('%s', [$distance]);
        }

        if ($overSpeed && $areaTriggerType && $limit) {
            return [
                '0' => $overSpeed,
                '1' => $areaTriggerType,
                '2' => $limit
            ];
        } elseif ($areaTriggerType && $limit) {
            return [
                '0' => $areaTriggerType,
                '1' => $limit
            ];
        }

        return $data;
    }

    /**
     * @param array $event
     * @return array
     */
    public function prepareEventSourceFields(array $event)
    {
        $eventDetails = [];
        $entity = ClassUtils::getRealClass($event['event']['entity']);
        $entityString = StringHelper::getClassName($event['event']['entity']);

        $eventDetails[self::EVENT_SOURCE_NAME] = $event[self::EVENT_SOURCE];
        $eventDetails[self::EVENT_SOURCE_TYPE] = $event['event'][self::EVENT_SOURCE];
        $eventDetails[self::EVENT_SOURCE_TEAM] = $event['details']['team'] ?? null;
        $eventDetails[sprintf('%sId', $entityString)] = $event['entityId'];

        switch ($entity) {
            case Event::ENTITY_TYPE_REMINDER:
            case Event::ENTITY_TYPE_SPEEDING:
            case Event::ENTITY_TYPE_AREA_HISTORY:
                $eventDetails[sprintf(
                    '%sId',
                    StringHelper::getClassName(Event::ENTITY_TYPE_VEHICLE)
                )] = $event['details']['vehicle']['id'] ?? null;
                break;
            case Event::ENTITY_TYPE_TRACKER_HISTORY:
                $eventDetails[self::EVENT_SOURCE_TEAM] = $event['details']['device']['team'];
                $eventDetails[sprintf(
                    '%sId',
                    StringHelper::getClassName(Event::ENTITY_TYPE_DEVICE)
                )] = $event['details']['deviceId'] ?? null;
                break;
            case Event::ENTITY_TYPE_VEHICLE:
                $eventDetails[sprintf(
                    '%sId',
                    StringHelper::getClassName(Event::ENTITY_TYPE_DEVICE)
                )] = $event['details']['deviceId'] ?? null;
                break;
            case Event::ENTITY_TYPE_DOCUMENT_RECORD:
                $eventDetails[sprintf(
                    '%sId',
                    StringHelper::getClassName(Event::ENTITY_TYPE_DOCUMENT)
                )] = $event['details']['document']['id'];

                if ($event['details']['document']['documentType'] === Document::DRIVER_DOCUMENT) {
                    $eventDetails[self::EVENT_SOURCE_TYPE] = Document::DRIVER_DOCUMENT;
                    $eventDetails['driverId'] = $event['details']['document']['driver']['id'];
                } else {
                    $eventDetails[sprintf(
                        '%sId',
                        StringHelper::getClassName(Event::ENTITY_TYPE_VEHICLE)
                    )] = $event['details']['document']['vehicle']['id'] ?? null;
                }
                break;
            case Event::ENTITY_TYPE_DOCUMENT:
                if ($event['details']['documentType'] === Document::DRIVER_DOCUMENT) {
                    $eventDetails[self::EVENT_SOURCE_TYPE] = Document::DRIVER_DOCUMENT;
                    $eventDetails['driverId'] = $event['details']['driver']['id'] ?? null;
                } else {
                    $eventDetails[sprintf(
                        '%sId',
                        StringHelper::getClassName(Event::ENTITY_TYPE_VEHICLE)
                    )] = $event['details']['vehicle']['id'] ?? null;
                }
                break;
            case Event::ENTITY_TYPE_ROUTE:
            case Event::ENTITY_TYPE_VEHICLE_ODOMETER:
                $eventDetails[sprintf(
                    '%sId',
                    StringHelper::getClassName(Event::ENTITY_TYPE_VEHICLE)
                )] = $event['details']['vehicleId'];
                break;
            case Event::ENTITY_TYPE_SERVICE_RECORD:
                $eventDetails[sprintf(
                    '%sId',
                    StringHelper::getClassName(Event::ENTITY_TYPE_VEHICLE)
                )] = $event['details']['repairVehicle']['id'] ?? null;
                break;
            case Event::ENTITY_TYPE_INVOICE:
                if ($event['entityId'] ?? null) {
                    $eventDetails['clientId'] = $this->em->getRepository(Invoice::class)
                        ->find($event['entityId'])?->getClient()?->getId();
                }

                break;
            case Event::ENTITY_TYPE_DIGITAL_FORM_ANSWER:
                $eventDetails[sprintf(
                    '%sId',
                    StringHelper::getClassName(Event::ENTITY_TYPE_DIGITAL_FORM)
                )] = $event['entityId'];

                $eventDetails[sprintf(
                    '%sId',
                    StringHelper::getClassName(Event::ENTITY_TYPE_VEHICLE)
                )] = $event['details']['vehicle']['id'];

                $eventDetails[sprintf(
                    '%sId',
                    StringHelper::getClassName(Event::ENTITY_TYPE_USER)
                )] = $event['details']['user']['id'];
                break;
            default:
                break;
        }

        return $eventDetails;
    }

    /**
     * @param $params
     * @param User $user
     * @param false $paginated
     * @return array
     */
    public function getEventLogExportData($params, User $user, $paginated = false)
    {
        /** @var ReportBuilderInterface $reportBuilder */
        $reportBuilder = $this->getEventLogExport($params, $user, $paginated);

        if ($reportBuilder instanceof ReportBuilderInterface) {
            return $this->translateEntityArrayForExport(
                $reportBuilder->getData(),
                $reportBuilder->getHeader(),
                EventLog::class
            );
        }

        return $this->translateEntityArrayForExport($reportBuilder, $params['fields'], EventLog::class);
    }

    /**
     * @param array $IOTypes
     * @param int $typeId
     * @return null
     */
    public function getNameIOType(array $IOTypes, int $typeId)
    {
        $IOType = array_map(
            function (TrackerIOType $IOType) {
                return $IOType->getLabel();
            },
            array_filter(
                $IOTypes,
                function (TrackerIOType $IOType) use ($typeId) {
                    return $IOType->getId() === $typeId;
                }
            )
        );

        return array_shift($IOType) ?? null;
    }

    /**
     * @param $params
     * @param User $user
     * @return array
     */
    public function prepareParams($params, User $user)
    {
        if (isset($params['eventTeam'])) {
            $params['eventTeam'] = ($params['eventTeam'] !== '-1') ? $params['eventTeam'] : $user->getTeam()->getId();
        } else {
            if ($user->isInClientTeam()) {
                $params['eventTeam'] = $user->getTeam()->getId();
            }
            if ($user->isInResellerTeam()) {
                $params['eventTeam'] = $this->em->getRepository(Reseller::class)
                    ->getResellerClientTeams($user->getReseller());
                $params['eventTeam'][] = $user->getTeamId();
            }

            if ($user->isInAdminTeam() && !$user->isSuperAdmin() && !$user->isAdmin()) {
                $params['eventTeam'] = $this->em->getRepository(Client::class)->getAdminClientTeams();
                $params['eventTeam'][] = $user->getTeamId();
            }
        }

        if (($params['startDate'] ?? null) && ($params['endDate'] ?? null)) {
            $params['formattedDate']['startDate'] = Carbon::parse($params['startDate'])->setTimezone('UTC')->format('c');
            $params['formattedDate']['endDate'] = Carbon::parse($params['endDate'])->setTimezone('UTC')->format('c');
        } elseif (isset($params['formattedDate'])) {
            $params['formattedDate']['startDate'] = $params['formattedDate']['gte'] ?? $params['startDate'] ?? null;
            $params['formattedDate']['endDate'] = $params['formattedDate']['lt'] ?? $params['endDate'] ?? null;
        }
//        $params['fields'] = array_merge(EventLog::DISPLAYED_VALUES, $params['fields'] ?? []);

        if ($user->needToCheckUserGroup()) {
            $params['vehicleId'] = $this->em->getRepository(UserGroup::class)->getUserVehiclesIdFromUserGroup($user);
        }

        if (isset($params['eventId'])) {
            /** @var Event $event */
            $event = $params['event'] = $this->em->getRepository(Event::class)->findOneBy(['id' => $params['eventId']]);

            return $this->getInternalMappedFilters($params, $event->getMappingFiltersByEvent());
        }

        return $params;
    }

    /**
     * @param array $params
     * @param array $filters
     * @return array
     */
    public function getInternalMappedFilters(array $params, ?array $filters): array
    {
        $fields = [];

        $handlerParams = $params;

        foreach ($filters as $keyFilter => $criteriaKey) {
            $entityValue = array_key_exists($keyFilter, $handlerParams);

            if ($entityValue === false) {
                continue;
            }

            $fields[$criteriaKey] = $handlerParams[$keyFilter];
        }

        $fields['sort'] = $params['sort'] ?? '-eventDate';

        if ($params['eventTeam'] ?? null) {
            $fields['entityTeamId'] = $params['eventTeam'];
        }

        if ($params['vehicleId'] ?? null) {
            $fields['vehicleId'] = $params['vehicleId'];
        }

        return $fields;
    }

    /**
     * Temporary check to determine whether the report can be generated in the new version
     *
     * @param $params
     * @return array|null
     */
    public function supportNewVersion($params): ?array
    {
        if (isset($params['eventId'])) {
            /** @var Event $event */
            $event = $this->em->getRepository(Event::class)->findOneBy(['id' => $params['eventId']]);

            return $event->getMappingFiltersByEvent();
        }

        return null;
    }

    /**
     * @param PaginationInterface $data
     * @param array $params
     * @param User $user
     * @return PaginationInterface
     */
    public function getEventLogView(PaginationInterface $data, array $params, User $user): PaginationInterface
    {
        $event = $params['event'] ?? null;
        $data->setItems($this->prepareEventLogData(['data' => $data->getItems()], $user, $event)['data']);

        return $data;
    }

    /**
     * @param $data
     * @param $params
     * @param User $user
     * @param $paginated
     * @return array
     * @throws \App\Service\EventLog\Exception\UndefinedEntityByEventLogException
     */
    public function getEventLogSqlExportData($data, $params, User $user, $paginated = false)
    {
        /** @var ReportBuilderInterface $reportBuilder */
        $reportBuilder = $this->getEventLogSqlExport($data, $params, $user, $paginated);
        if ($reportBuilder instanceof ReportBuilderInterface) {
            return $this->translateEntityArrayForExport(
                $reportBuilder->getData(),
                $reportBuilder->getHeader(),
                EventLog::class
            );
        }

        return $this->translateEntityArrayForExport($reportBuilder, $params['fields'], EventLog::class);
    }

    /**
     * @param $data
     * @param array $params
     * @param User $user
     * @param bool $paginated
     * @return ReportBuilderInterface
     * @throws \App\Service\EventLog\Exception\UndefinedEntityByEventLogException
     */
    public function getEventLogSqlExport($data, array $params, User $user, bool $paginated = true)
    {
        $eventLogData = $data->execute();

        $event = null;
        if ($params['eventId'] ?? null) {
            /** @var Event $event */
            $event = $this->em->getRepository(Event::class)->findOneBy(['id' => $params['eventId']]);
        }

        $teamNotificationByEvent = $this->em->getRepository(Notification::class)
            ->getNotificationsByTeam($user->getTeam(), $event);

        // TODO - change the forwarding of branch for drawing the event log
        $digitalIOTypes = $this->em->getRepository(TrackerIOType::class)->findAll();

        $handlerByEvent = $this->entityHandlerFactory->getInstance(
            $event,
            $user,
            $this->translator,
            $teamNotificationByEvent,
            $digitalIOTypes
        );

        return (new ReportBuilder($handlerByEvent))
            ->build($eventLogData, $user, $params['fields']);
    }
}
