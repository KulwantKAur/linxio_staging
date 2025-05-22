<?php

namespace App\Service\Notification;

use App\Entity\AreaHistory;
use App\Entity\Asset;
use App\Entity\Client;
use App\Entity\Device;
use App\Entity\DigitalFormAnswer;
use App\Entity\Document;
use App\Entity\DocumentRecord;
use App\Entity\Idling;
use App\Entity\Notification\Event;
use App\Entity\Reminder;
use App\Entity\Route;
use App\Entity\ServiceRecord;
use App\Entity\Tracker\TrackerAuthUnknown;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistoryIO;
use App\Entity\Tracker\TrackerHistorySensor;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Entity\VehicleOdometer;
use App\Service\Notification\Placeholder\EntityPlaceholder\EntityPlaceholderNull;
use App\Service\Notification\Placeholder\Factory\EventEntityHandlerFactory;
use App\Service\Notification\Placeholder\Interfaces\EventEntityHandlerInterface;
use App\Service\Notification\Placeholder\Factory\PlaceholderFactory;
use App\Service\Notification\Placeholder\Interfaces\PlaceholderInterface;
use App\Util\DateHelper;
use App\Util\MetricHelper;

class EntityPlaceholderService
{
    private string $appFrontUrl;

    /** @var PlaceholderFactory */
    private PlaceholderFactory $placeholderFactory;

    /** @var EventEntityHandlerFactory */
    private EventEntityHandlerFactory $eventEntityHandlerFactory;

    public const DEFAULT_UNKNOWN = '--';

    public function __construct(
        string $appFrontUrl,
        PlaceholderFactory $placeholderFactory,
        EventEntityHandlerFactory $eventEntityHandlerFactory
    ) {
        $this->appFrontUrl = $appFrontUrl;
        $this->placeholderFactory = $placeholderFactory;
        $this->eventEntityHandlerFactory = $eventEntityHandlerFactory;
    }

    public function generatePlaceholders(Event $event, $entity, array $context = [], ?User $user = null): array
    {
        try {
            $entityHandlerByEvent = $this->eventEntityHandlerFactory->getInstance(
                $event,
                $entity,
                $this->appFrontUrl,
                $context,
            );

            $placeholderByEvent = $this->placeholderFactory->getInstance($event);

            return $placeholderByEvent->getPlaceholder($entityHandlerByEvent, $user);
        } catch (\Exception $e) {
            return (new EntityPlaceholderNull())->getPlaceholder();
        }
    }

    protected function getFrontendLinks(Event $event, $entity)
    {
        switch ($event->getName()) {
            case Event::USER_CREATED:
            case Event::USER_BLOCKED:
            case Event::USER_DELETED:
            case Event::USER_PWD_RESET:
            case Event::USER_CHANGED_NAME:
            case Event::ADMIN_USER_CREATED:
            case Event::ADMIN_USER_BLOCKED:
            case Event::ADMIN_USER_DELETED:
            case Event::ADMIN_USER_PWD_RESET:
            case Event::ADMIN_USER_CHANGED_NAME:
                return [
                    'data_url' => $this->getUserFrontendLink($entity)
                ];
            case Event::TRACKER_VOLTAGE:
            case Event::VEHICLE_REASSIGNED:
            case Event::VEHICLE_OVERSPEEDING:
            case Event::EXCEEDING_SPEED_LIMIT:
            case Event::VEHICLE_OVERSPEEDING_INSIDE_GEOFENCE:
            case Event::VEHICLE_CREATED:
            case Event::VEHICLE_DELETED:
            case Event::VEHICLE_CHANGED_REGNO:
            case Event::VEHICLE_CHANGED_MODEL:
            case Event::VEHICLE_UNAVAILABLE:
            case Event::VEHICLE_OFFLINE:
            case Event::VEHICLE_GEOFENCE_ENTER:
            case Event::VEHICLE_GEOFENCE_LEAVE:
            case Event::VEHICLE_DRIVING_WITHOUT_DRIVER:
            case Event::VEHICLE_LONG_STANDING:
            case Event::VEHICLE_LONG_DRIVING:
            case Event::VEHICLE_MOVING:
            case Event::VEHICLE_EXCESSING_IDLING:
            case Event::VEHICLE_ONLINE:
            case Event::DIGITAL_FORM_IS_NOT_COMPLETED:
            case Event::SENSOR_IO_STATUS:
                switch ($event->getEntity()) {
                    case Event::ENTITY_TYPE_TRACKER_HISTORY:
                    case Event::ENTITY_TYPE_TRACKER_HISTORY_IO:
                    case Event::ENTITY_TYPE_ROUTE:
                    case Event::ENTITY_TYPE_AREA_HISTORY:
                    case Event::ENTITY_TYPE_IDLING:
                        $vehicleId = $entity->getVehicle() ? $entity->getVehicle()->getId() : null;
                        $driverId = $entity->getVehicle()
                            ? ($entity->getVehicle()->getDriver()
                                ? $entity->getVehicle()->getDriver()->getId()
                                : null)
                            : null;
                        break;
                    case Event::ENTITY_TYPE_VEHICLE:
                        $vehicleId = $entity->getId() ? $entity->getId() : null;
                        $driverId = $entity->getDriver() ? $entity->getDriver()->getId() : null;
                        break;
                    default:
                        $vehicleId = null;
                        $driverId = null;
                        break;
                }

                return [
                    'vehicle_url' => $vehicleId
                        ? vsprintf(
                            'Vehicle page: %s/client/fleet/%d/specification',
                            [$this->appFrontUrl, $vehicleId]
                        )
                        : null,
                    'driver_url' => $driverId
                        ? vsprintf(
                            'Driver page: %s/client/drivers/%d/profile-info',
                            [$this->appFrontUrl, $driverId]
                        )
                        : null
                ];
            case Event::SERVICE_REPAIR_ADDED:
                /** @var ServiceRecord $entity */
                $serviceRepairId = $entity->getId();
                if ($entity->getRepairVehicle()) {
                    $vehicleId = $entity->getRepairVehicle() ? $entity->getRepairVehicle()->getId() : null;
                    $data_url = ($vehicleId && $serviceRepairId)
                        ? vsprintf(
                            'Service repair page: %s/client/fleet/%d/repair-costs/%d',
                            [$this->appFrontUrl, $vehicleId, $serviceRepairId]
                        )
                        : null;
                }
                if ($entity->getRepairAsset()) {
                    $assetId = $entity->getRepairAsset() ? $entity->getRepairAsset()->getId() : null;
                    $data_url = ($assetId && $serviceRepairId)
                        ? vsprintf(
                            'Service repair page: %s/client/asset/%d/repair-costs/%d',
                            [$this->appFrontUrl, $assetId, $serviceRepairId]
                        )
                        : null;
                }

                return ['data_url' => $data_url ?? ''];
            case Event::SERVICE_RECORD_ADDED:
            case Event::SERVICE_REMINDER_DONE:
            case Event::SERVICE_REMINDER_DELETED:
            case Event::SERVICE_REMINDER_SOON:
            case Event::SERVICE_REMINDER_EXPIRED:
                switch ($event->getEntity()) {
                    case Event::ENTITY_TYPE_SERVICE_RECORD:
                        /** @var ServiceRecord $entity */
                        $reminderId = $entity->getReminderId();
                        $vehicleId = $entity->getServiceRecordVehicle() ? $entity->getServiceRecordVehicle()->getId() : null;
                        $assetId = $entity->getRepairAsset() ? $entity->getRepairAsset()->getId() : null;
                        break;
                    case Event::ENTITY_TYPE_REMINDER:
                        /** @var Reminder $entity */
                        $reminderId = $entity->getId();
                        $vehicleId = $entity->getVehicle() ? $entity->getVehicle()->getId() : null;
                        $assetId = $entity->getAsset() ? $entity->getAsset()->getId() : null;
                        break;
                    default:
                        $deviceId = null;
                        $vehicleId = null;
                        $assetId = null;
                        $reminderId = null;
                        break;
                }
                if ($vehicleId && $reminderId) {
                    $dataUrl = vsprintf(
                        'Service reminder page: %s/client/fleet/%d/service-reminders/%d',
                        [$this->appFrontUrl, $vehicleId, $reminderId]
                    );
                }
                if ($assetId && $reminderId) {
                    $dataUrl = vsprintf(
                        'Service reminder page: %s/client/asset/%d/service-reminders/%d',
                        [$this->appFrontUrl, $assetId, $reminderId]
                    );
                }

                return ['data_url' => $dataUrl ?? null];
            case Event::DOCUMENT_RECORD_ADDED:
            case Event::DOCUMENT_DELETED:
            case Event::DOCUMENT_EXPIRED:
            case Event::DOCUMENT_EXPIRE_SOON:
            case Event::DRIVER_DOCUMENT_EXPIRE_SOON:
            case Event::DRIVER_DOCUMENT_EXPIRED:
            case Event::DRIVER_DOCUMENT_DELETED:
            case Event::DRIVER_DOCUMENT_RECORD_ADDED:
            case Event::ASSET_DOCUMENT_EXPIRE_SOON:
            case Event::ASSET_DOCUMENT_EXPIRED:
            case Event::ASSET_DOCUMENT_DELETED:
            case Event::ASSET_DOCUMENT_RECORD_ADDED:
                switch ($event->getEntity()) {
                    case Event::ENTITY_TYPE_DOCUMENT_RECORD:
                        /** @var DocumentRecord $entity */
                        $documentId = $entity->getId();

                        if ($entity->getDocument()->isDriverDocument()) {
                            $driverId = $entity->getDriver() ? $entity->getDriver()->getId() : null;
                            $driverName = $entity->getDriver() ? $entity->getDriver()->getFullName() : self::DEFAULT_UNKNOWN;

                            $data_url = ($driverId && $documentId)
                                ? vsprintf(
                                    'Document page: %s/client/drivers/%d/documents/%d',
                                    [$this->appFrontUrl, $driverId, $documentId]
                                )
                                : null;
                            $data_by_type = sprintf('(driver - %s)', $driverName);
                        } elseif ($entity->getDocument()->isVehicleDocument()) {
                            $vehicleId = $entity->getVehicle() ? $entity->getVehicle()->getId() : null;
                            $vehicleRegNo = $entity->getVehicle() ? $entity->getVehicle()->getRegNo() : null;

                            $data_url = ($vehicleId && $documentId)
                                ? vsprintf(
                                    'Document page: %s/client/fleet/%d/documents/%d',
                                    [$this->appFrontUrl, $vehicleId, $documentId]
                                )
                                : null;
                            $data_by_type = sprintf('(vehicle - %s)', $vehicleRegNo);
                        } elseif ($entity->getDocument()->isAssetDocument()) {
                            $assetId = $entity->getAsset() ? $entity->getAsset()->getId() : null;
                            $AssetName = $entity->getAsset() ? $entity->getAsset()->getName() : null;

                            $data_url = ($assetId && $documentId)
                                ? vsprintf(
                                    'Document page: %s/client/asset/%d/documents/%d',
                                    [$this->appFrontUrl, $assetId, $documentId]
                                )
                                : null;
                            $data_by_type = sprintf('(asset - %s)', $AssetName);
                        }
                        break;
                    case Event::ENTITY_TYPE_DOCUMENT:
                        /** @var Document $entity */
                        $documentId = $entity->getId();

                        if ($entity->isDriverDocument()) {
                            $driverId = $entity->getVehicle()
                                ? $entity->getVehicle()->getDriver()
                                    ? $entity->getVehicle()->getDriver()
                                    : null
                                : null;
                            $driverName = $entity->getVehicle()->getDriver()
                                ? $entity->getVehicle()->getDriver()->getFullName()
                                : self::DEFAULT_UNKNOWN;

                            $data_url = ($driverId && $documentId)
                                ? vsprintf(
                                    'Document page: %s/client/drivers/%d/documents/%d',
                                    [$this->appFrontUrl, $driverId, $documentId]
                                )
                                : null;
                            $data_by_type = sprintf('(driver - %s)', $driverName);
                        } else {
                            $vehicleId = $entity->getVehicle()?->getId();
                            $vehicleRegNo = $entity->getVehicle()?->getRegNo();

                            $data_url = ($vehicleId && $documentId)
                                ? vsprintf(
                                    'Document page: %s/client/fleet/%d/documents/%d',
                                    [$this->appFrontUrl, $vehicleId, $documentId]
                                )
                                : null;
                            $data_by_type = sprintf('(vehicle - %s)', $vehicleRegNo);
                        }
                        break;
                    default:
                        $data_url = null;
                        $data_by_type = null;
                        break;
                }
                return [
                    'data_url' => $data_url,
                    'data_by_type' => $data_by_type,
                ];
            case Event::DEVICE_OFFLINE:
            case Event::DEVICE_IN_STOCK:
            case Event::DEVICE_UNAVAILABLE:
            case Event::DEVICE_DELETED:
                /** @var Device $entity */
                $deviceId = $entity->getId();

                return [
                    'data_url' => null,
//                    'data_url' => ($deviceId && $entity->getTeam()->isAdminTeam())
//                        ? vsprintf(
//                            'Device page: %s/admin/devices/%d/status',
//                            [$this->appFrontUrl, $deviceId]
//                        )
//                        : null
                ];
            case Event::DIGITAL_FORM_WITH_FAIL:
                return [
                    'data_url' => vsprintf(
                        'Form page: %s/client/reports/summary_details/vehicle_inspections/%d',
                        [$this->appFrontUrl, $entity->getId()]
                    )
                ];
            default:
                return [];
        }
    }

    /**
     * @param $entity
     * @return string
     */
    protected function getUserFrontendLink($entity): string
    {
        if ($entity->getTeam()->isAdminTeam()) {
            return vsprintf(
                '%s/admin/team/users/%d',
                [$this->appFrontUrl, $entity->getId()]
            );
        } else {
            return vsprintf(
                '%s/admin/clients/%d/users/%d',
                [$this->appFrontUrl, $entity->getTeam()->getId(), $entity->getId()]
            );
        }
    }
}
