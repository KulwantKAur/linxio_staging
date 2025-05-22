<?php

namespace App\Service\EventLog\Factory;

use App\Entity\Notification\Event;
use App\Events\Notification\NotificationEvent;
use App\Service\EventLog\Mapper\AreaEnterLeaveEventLog;
use App\Service\EventLog\Mapper\AssetEventLog;
use App\Service\EventLog\Mapper\ClientEventLog;
use App\Service\EventLog\Mapper\DeviceEventLog;
use App\Service\EventLog\Mapper\DocumentEventLog;
use App\Service\EventLog\Mapper\DocumentRecordEventLog;
use App\Service\EventLog\Mapper\IdlingEventLog;
use App\Service\EventLog\Mapper\InvoiceEventLog;
use App\Service\EventLog\Mapper\ReminderEventLog;
use App\Service\EventLog\Mapper\RouteEventLog;
use App\Service\EventLog\Mapper\ServiceRecordEventLog;
use App\Service\EventLog\Mapper\SpeedingEventLog;
use App\Service\EventLog\Mapper\TeamEventLog;
use App\Service\EventLog\Mapper\TrackerHistoryEventLog;
use App\Service\EventLog\Mapper\TrackerHistoryIOEventLog;
use App\Service\EventLog\Mapper\TrackerHistorySensorEventLog;
use App\Service\EventLog\Mapper\UnknownDeviceEventLog;
use App\Service\EventLog\Mapper\UserEventLog;
use App\Service\EventLog\Mapper\VehicleEventLog;
use App\Service\EventLog\Mapper\VehicleOdometerEventLog;
use App\Service\EventLog\Mapper\DigitalFormEventLog;
use App\Service\MapService\MapServiceInterface;
use Doctrine\Common\Util\ClassUtils;

class EventLogFactory
{
    public static function getInstance(Event $event, NotificationEvent $entity, MapServiceInterface $mapService)
    {
        $objEntity = $entity->getEntity();
        $currentUser = $entity->getCurrentUser();
        $context = $entity->getContext();
        $eventClass = ClassUtils::getClass($entity->getEntity());

        switch ($eventClass) {
            case Event::ENTITY_TYPE_USER:
                $entityData = new UserEventLog($objEntity, $currentUser, $event, $context);
                $entityData->getEntityCurrentAction();

                return $entityData;
            case Event::ENTITY_TYPE_CLIENT:
                $entityData = new ClientEventLog($objEntity, $currentUser, $event);
                $entityData->getEntityCurrentAction();

                return $entityData;
            case Event::ENTITY_TYPE_VEHICLE:
                $entityData = new VehicleEventLog($objEntity, $currentUser, $event, $context, $entity->getDt());
                $entityData->getEntityCurrentAction();

                return $entityData;
            case Event::ENTITY_TYPE_DEVICE:
                $entityData = new DeviceEventLog($objEntity, $currentUser, $event);
                $entityData->getEntityCurrentAction();

                return $entityData;
            case Event::ENTITY_TYPE_REMINDER:
                $entityData = new ReminderEventLog($objEntity, $currentUser, $event);
                $entityData->getEntityCurrentAction();

                return $entityData;
            case Event::ENTITY_TYPE_SERVICE_RECORD:
                $entityData = new ServiceRecordEventLog($objEntity, $currentUser, $event);
                $entityData->getEntityCurrentAction();

                return $entityData;

            case Event::ENTITY_TYPE_AREA_HISTORY:
                $entityData = new AreaEnterLeaveEventLog($objEntity, $currentUser, $event, $context, $entity->getDt());
                $entityData->getEntityCurrentAction();

                return $entityData;

            case Event::ENTITY_TYPE_UNKNOWN_DEVICE_AUTH:
                $entityData = new UnknownDeviceEventLog($objEntity, $event);

                return $entityData;

            case Event::ENTITY_TYPE_TRACKER_HISTORY:
                $entityData = new TrackerHistoryEventLog($objEntity, $currentUser, $event, $mapService, $context);
                $entityData->getEntityCurrentAction();

                return $entityData;

            case Event::ENTITY_TYPE_SPEEDING:
                $entityData = new SpeedingEventLog($objEntity, $currentUser, $event);
                $entityData->getEntityCurrentAction();

                return $entityData;

            case Event::ENTITY_TYPE_ROUTE:
                $entityData = new RouteEventLog($objEntity, $currentUser, $event, $context);
                $entityData->getEntityCurrentAction();

                return $entityData;

            case Event::ENTITY_TYPE_DOCUMENT:
                $entityData = new DocumentEventLog($objEntity, $currentUser, $event);
                $entityData->getEntityCurrentAction();

                return $entityData;

            case Event::ENTITY_TYPE_DOCUMENT_RECORD:
                $entityData = new DocumentRecordEventLog($objEntity, $currentUser, $event);
                $entityData->getEntityCurrentAction();

                return $entityData;
            case Event::ENTITY_TYPE_IDLING:
                $entityData = new IdlingEventLog($objEntity, $currentUser, $event);
                $entityData->getEntityCurrentAction();

                return $entityData;

            case Event::ENTITY_TYPE_VEHICLE_ODOMETER:
                $entityData = new VehicleOdometerEventLog($objEntity, $currentUser, $event, $context);
                $entityData->getEntityCurrentAction();

                return $entityData;
            case Event::ENTITY_TYPE_DIGITAL_FORM_ANSWER:
                $entityData = new DigitalFormEventLog($objEntity, $currentUser, $event, $context);
                $entityData->getEntityCurrentAction();

                return $entityData;

            case Event::ENTITY_TYPE_TRACKER_HISTORY_SENSOR:
                $entityData = new TrackerHistorySensorEventLog($objEntity, $currentUser, $event, $context);
                $entityData->getEntityCurrentAction();

                return $entityData;

            case Event::ENTITY_TYPE_TRACKER_HISTORY_IO:
                $entityData = new TrackerHistoryIOEventLog($objEntity, $currentUser, $event, $context);
                $entityData->getEntityCurrentAction();

                return $entityData;
            case Event::ENTITY_TYPE_ASSET:
                $entityData = new AssetEventLog($objEntity, $currentUser, $event, $context);
                $entityData->getEntityCurrentAction();

                return $entityData;
            case Event::ENTITY_TYPE_INVOICE:
                $entityData = new InvoiceEventLog($objEntity, $currentUser, $event, $context);
                $entityData->getEntityCurrentAction();

                return $entityData;
            case Event::ENTITY_TYPE_TEAM:
                $entityData = new TeamEventLog($objEntity, $currentUser, $event, $context);
                $entityData->getEntityCurrentAction();

                return $entityData;
            default:
                throw new \Exception('Unsupported event class name: ' . $eventClass);
        }
    }
}
