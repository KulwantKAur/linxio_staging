<?php

namespace App\Service\Notification\Placeholder\Factory;

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
use App\Service\Notification\Placeholder\EntityHandler\AreaHistoryEntityHandler;
use App\Service\Notification\Placeholder\EntityHandler\ClientEntityHandler;
use App\Service\Notification\Placeholder\EntityHandler\DeviceEntityHandler;
use App\Service\Notification\Placeholder\EntityHandler\DigitalFormAnswerEntityHandler;
use App\Service\Notification\Placeholder\EntityHandler\DocumentEntityHandler;
use App\Service\Notification\Placeholder\EntityHandler\DocumentRecordEntityHandler;
use App\Service\Notification\Placeholder\EntityHandler\IdlingEntityHandler;
use App\Service\Notification\Placeholder\EntityHandler\InvoiceEntityHandler;
use App\Service\Notification\Placeholder\EntityHandler\ReminderEntityHandler;
use App\Service\Notification\Placeholder\EntityHandler\RouteEntityHandler;
use App\Service\Notification\Placeholder\EntityHandler\ServiceRecordEntityHandler;
use App\Service\Notification\Placeholder\EntityHandler\TrackerAuthUnknownEntityHandler;
use App\Service\Notification\Placeholder\EntityHandler\TrackerHistoryEntityHandler;
use App\Service\Notification\Placeholder\EntityHandler\TrackerHistoryIOEntityHandler;
use App\Service\Notification\Placeholder\EntityHandler\TrackerHistorySensorEntityHandler;
use App\Service\Notification\Placeholder\EntityHandler\UserEntityHandler;
use App\Service\Notification\Placeholder\EntityHandler\VehicleEntityHandler;
use App\Service\Notification\Placeholder\EntityHandler\VehicleOdometerEntityHandler;
use App\Service\Notification\Placeholder\Exception\UndefinedEntityHandlerException;
use App\Service\Notification\Placeholder\Interfaces\EventEntityHandlerInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventEntityHandlerFactory
{
    protected TranslatorInterface $translator;

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
        Asset::class => VehicleOdometerEntityHandler::class,
        Invoice::class => InvoiceEntityHandler::class,
    ];

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param Event $event
     * @param object $entity
     * @param string $appFrontUrl
     * @param array $context
     * @return EventEntityHandlerInterface
     * @throws UndefinedEntityHandlerException
     */
    public function getInstance(
        Event $event,
        object $entity,
        string $appFrontUrl,
        array $context = []
    ): EventEntityHandlerInterface {
        $entityClass = ClassUtils::getClass($entity);

        if (!array_key_exists($entityClass, self::$availableEntityHandler)) {
            throw new UndefinedEntityHandlerException(
                sprintf(
                    'Unsupported class "%s" by event: "%s".',
                    $entityClass,
                    $event->getName()
                )
            );
        }

        $entityHandlerClass = self::$availableEntityHandler[$entityClass];

        return (new $entityHandlerClass($this->translator, $entity, $appFrontUrl, $context));
    }
}
