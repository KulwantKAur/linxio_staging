<?php

namespace App\Service\EventLog\Report\ReportBuilder;

use App\Entity\EventLog\EventLog;
use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use App\Entity\Notification\Template;
use App\Entity\Tracker\TrackerIOType;
use App\Entity\User;
use App\Service\EventLog\Interfaces\ReportHandlerInterface;
use App\Service\Sensor\SensorService;
use App\Util\DateHelper;
use App\Util\MetricHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractEntityHandler implements ReportHandlerInterface
{
    protected ?Event $event;
    protected User $user;
    protected TranslatorInterface $translator;
    protected array $teamNotificationByEvent;
    protected array $header;
    protected array $digitalIOTypes;

    protected $notificationData;

    public const TYPE_ADDITIONAL_INFO = 'additional_info';
    public const TYPE_BASIC_INFO = 'basic_info';
    public const TYPE_SENSOR_ADDITIONAL_INFO = 'sensor_additional_info';

    /**
     * @param Event|null $event
     * @param User $user
     * @param TranslatorInterface $translator
     * @param array $teamNotificationByEvent
     * @param array $digitalIOTypes
     */
    public function __construct(
        ?Event $event,
        User $user,
        TranslatorInterface $translator,
        array $teamNotificationByEvent,
        array $digitalIOTypes

    ) {
        $this->event = $event;
        $this->user = $user;
        $this->translator = $translator;
        $this->teamNotificationByEvent = $teamNotificationByEvent;
        $this->digitalIOTypes = $digitalIOTypes;
    }

    /**
     * @return ?Event
     */
    public function getEvent(): ?Event
    {
        return $this->event;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return array
     */
    public function getTeamNotificationByEvent(): array
    {
        return $this->teamNotificationByEvent;
    }

    /**
     * @return array
     */
    public function getDigitalIOTypes(): array
    {
        return $this->digitalIOTypes;
    }

    /**
     * @return array
     */
    public function getHeader(): array
    {
        return $this->getEvent() ? $this->getEvent()->getHeaderByEvent() : EventLog::DEFAULT_EXPORT_VALUES;
    }

    /**
     * @param EventLog $eventLog
     * @param array $fields
     * @return array
     * @throws \Exception
     */
    public function toExport(EventLog $eventLog, array $fields = []): array
    {
        $data = $eventLog->toArray($fields);

        if (array_key_exists(EventLog::EVENT_LOG_ID, $fields)) {
            $data[$fields[EventLog::EVENT_LOG_ID]] = $eventLog->getId();
        }

        if (array_key_exists(EventLog::DATE, $fields)) {
            $data[$fields[EventLog::DATE]] = DateHelper::formatDate(
                $eventLog->getFormattedDate(),
                DateHelper::FORMAT_DATE_SHORT_TIME,
                $this->getUser()->getTimezone()
            );
        }

        if (array_key_exists(EventLog::IMPORTANCE, $fields)) {
            $data[$fields[EventLog::IMPORTANCE]] = $eventLog->getEvent()->getImportanceName();
        }

        if (array_key_exists(EventLog::TRIGGERED_DETAILS, $fields)) {
            $data[$fields[EventLog::TRIGGERED_DETAILS]] = $eventLog->getTriggeredDetails();
        }

        if (array_key_exists(EventLog::EVENT_TEAM, $fields)) {
            $data[$fields[EventLog::EVENT_TEAM]] = $eventLog->getEventTeam();
        }

        if (array_key_exists(EventLog::EVENT_SOURCE_TYPE, $fields)) {
            $data[$fields[EventLog::EVENT_SOURCE_TYPE]] = $eventLog->getEventSourceType();
        }

        if (array_key_exists(EventLog::EVENT_TYPE, $fields)) {
            $data[$fields[EventLog::EVENT_TYPE]] = $eventLog->getEvent()->getAlias();
        }

        if (array_key_exists(EventLog::EVENT_SOURCE, $fields)) {
            $data[$fields[EventLog::EVENT_SOURCE]] = $eventLog->getEventSource();
        }

        $this->notificationData = !empty($eventLog->getNotificationsList())
            ? $this->getNotifications($eventLog->getNotificationsList(), $this->getTeamNotificationByEvent())
            : null;

        if (array_key_exists(EventLog::NTF_LIST, $fields)) {
            $data[$fields[EventLog::NTF_LIST]] = !empty($this->notificationData)
                ? $this->getNotificationsInfo(
                    $this->notificationData,
                    ['title'],
                    $eventTeam ?? []
                )
                : null;

            $data[$fields[EventLog::NTF_LIST]] = $data[$fields[EventLog::NTF_LIST]]
                ? implode(
                    ', ',
                    array_map(
                        function ($ntf) {
                            return $ntf['title'] ?? null;
                        },
                        $data[$fields[EventLog::NTF_LIST]]
                    )
                )
                : null;
        }

        if (array_key_exists(EventLog::LIMIT, $fields)) {
            $data[$fields[EventLog::LIMIT]] = !empty($this->notificationData)
                ? $this->getNotificationsInfo(
                    $this->notificationData,
                    [Notification::ADDITIONAL_PARAMS],
                    [],
                    self::TYPE_ADDITIONAL_INFO
                )
                : null;

            $data[$fields[EventLog::LIMIT]] = $data[$fields[EventLog::LIMIT]]
                ? implode(
                    ', ',
                    array_map(
                        function ($limit) {
                            return implode(', ', $limit) ?? null;
                        },
                        $data[$fields[EventLog::LIMIT]]
                    )
                )
                : null;
        }

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

        return  $data;
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
}
