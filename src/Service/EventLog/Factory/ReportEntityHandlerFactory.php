<?php

namespace App\Service\EventLog\Factory;

use App\Entity\AreaHistory;
use App\Entity\Asset;
use App\Entity\Client;
use App\Entity\Device;
use App\Entity\DigitalFormAnswer;
use App\Entity\Document;
use App\Entity\DocumentRecord;
use App\Entity\Idling;
use App\Entity\Invoice;
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
use App\Service\EventLog\Exception\UndefinedEntityByEventLogException;
use App\Service\EventLog\Interfaces\ReportHandlerInterface;
use App\Service\EventLog\Report\ReportBuilder\EntityHandler\AreaHistoryEntityHandler;
use App\Service\EventLog\Report\ReportBuilder\EntityHandler\AssetEntityHandler;
use App\Service\EventLog\Report\ReportBuilder\EntityHandler\ClientEntityHandler;
use App\Service\EventLog\Report\ReportBuilder\EntityHandler\DeviceEntityHandler;
use App\Service\EventLog\Report\ReportBuilder\EntityHandler\DigitalFormAnswerEntityHandler;
use App\Service\EventLog\Report\ReportBuilder\EntityHandler\DocumentEntityHandler;
use App\Service\EventLog\Report\ReportBuilder\EntityHandler\DocumentRecordEntityHandler;
use App\Service\EventLog\Report\ReportBuilder\EntityHandler\EntityHandlerWithoutEvent;
use App\Service\EventLog\Report\ReportBuilder\EntityHandler\IdlingEntityHandler;
use App\Service\EventLog\Report\ReportBuilder\EntityHandler\ReminderEntityHandler;
use App\Service\EventLog\Report\ReportBuilder\EntityHandler\RouteEntityHandler;
use App\Service\EventLog\Report\ReportBuilder\EntityHandler\ServiceRecordEntityHandler;
use App\Service\EventLog\Report\ReportBuilder\EntityHandler\TrackerAuthUnknownEntityHandler;
use App\Service\EventLog\Report\ReportBuilder\EntityHandler\TrackerHistoryEntityHandler;
use App\Service\EventLog\Report\ReportBuilder\EntityHandler\TrackerHistoryIOEntityHandler;
use App\Service\EventLog\Report\ReportBuilder\EntityHandler\TrackerHistorySensorEntityHandler;
use App\Service\EventLog\Report\ReportBuilder\EntityHandler\UserEntityHandler;
use App\Service\EventLog\Report\ReportBuilder\EntityHandler\VehicleEntityHandler;
use App\Service\EventLog\Report\ReportBuilder\EntityHandler\VehicleOdometerEntityHandler;
use App\Service\Notification\Placeholder\EntityHandler\InvoiceEntityHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ReportEntityHandlerFactory
 */
class ReportEntityHandlerFactory
{
    protected static array $availableEntityHandler = [
        User::class => UserEntityHandler::class,
        AreaHistory::class => AreaHistoryEntityHandler::class,
        Client::class => ClientEntityHandler::class,
        Idling::class => IdlingEntityHandler::class,
        Device::class => DeviceEntityHandler::class,
        DigitalFormAnswer::class => DigitalFormAnswerEntityHandler::class,
        Document::class => DocumentEntityHandler::class,
        DocumentRecord::class => DocumentRecordEntityHandler::class,
        Reminder::class => ReminderEntityHandler::class,
        ServiceRecord::class => ServiceRecordEntityHandler::class,
        TrackerAuthUnknown::class => TrackerAuthUnknownEntityHandler::class,
        TrackerHistoryIO::class => TrackerHistoryIOEntityHandler::class,
        VehicleOdometer::class => VehicleOdometerEntityHandler::class,
        Route::class => RouteEntityHandler::class,
        TrackerHistory::class => TrackerHistoryEntityHandler::class,
        TrackerHistorySensor::class => TrackerHistorySensorEntityHandler::class,
        Vehicle::class => VehicleEntityHandler::class,
        Asset::class => AssetEntityHandler::class,
        Invoice::class => InvoiceEntityHandler::class,
    ];

    /**
     * @param Event|null $event
     * @param User $user
     * @param TranslatorInterface $translator
     * @param array $teamNotificationByEvent
     * @param array $digitalIOTypes
     * @return ReportHandlerInterface
     * @throws UndefinedEntityByEventLogException
     */
    public function getInstance(
        ?Event $event,
        User $user,
        TranslatorInterface $translator,
        array $teamNotificationByEvent = [],
        array $digitalIOTypes = []
    ): ReportHandlerInterface {
        if (!$event) {
            return new EntityHandlerWithoutEvent($event, $user, $translator, $teamNotificationByEvent, $digitalIOTypes);
        }

        $entity = $event->getEntity();

        if (!array_key_exists($entity, self::$availableEntityHandler)) {
            throw new UndefinedEntityByEventLogException(
                sprintf(
                    'Unsupported class "%s" by event: "%s".',
                    $entity,
                    $event->getName()
                )
            );
        }
        $entityHandler = self::$availableEntityHandler[$entity];

        return (new $entityHandler($event, $user, $translator, $teamNotificationByEvent, $digitalIOTypes));
    }
}
