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
use App\Entity\Invoice;
use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use App\Entity\Reminder;
use App\Entity\Route;
use App\Entity\ServiceRecord;
use App\Entity\Speeding;
use App\Entity\Team;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistoryIO;
use App\Entity\Tracker\TrackerHistorySensor;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Entity\VehicleOdometer;
use App\Service\EventLog\EventLogService;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;

class NotificationCollectorService
{
    public function __construct(
        private readonly EntityManager $em,
        private readonly EntityPlaceholderService $placeholderService,
        private readonly ScopeService $scopeService,
        private readonly RecipientService $recipientService,
        private readonly MessageService $messageService,
        private readonly EventLogService $eventLogService
    ) {
    }

    public function collect(Event $event, $entity, $eventLog, \DateTime $dt, array $context = []): void
    {
        $notificationsRepo = $this->em->getRepository(Notification::class);
        $entityTeam = $this->getEntityOwnerTeam($entity);

        //ignore ntf for billing blocked clients
        if (in_array($entityTeam?->getClient()?->getStatus(), Client::STATUS_BLOCKED_NTF)
            && $event->getName() !== Event::INVOICE_OVERDUE_BLOCKED) {
            return;
        }

        switch ($event->getType()) {
            case Event::TYPE_SYSTEM:
                $notifications = $notificationsRepo->getTeamNotifications(
                    $event,
                    null,
                    $dt,
                    $entity,
                    $context
                );
                break;
            case Event::TYPE_USER:
                $notifications = $notificationsRepo->getTeamNotifications(
                    $event,
                    $this->getEntityOwnerTeam($entity),
                    $dt,
                    $entity,
                    $context
                );
                break;
            default:
                return;
        }

        $notifications = $this->scopeService->filterNotifications($notifications, $entity, $context);
        if ($eventLog) {
            $this->eventLogService->update($eventLog, $notifications);
        }

//        $placeholders = $this->placeholderService->generatePlaceholders($event, $entity, $context);

        foreach ($notifications as $notification) {
            $recipients = $this->recipientService->getNotificationRecipients($notification, $entity);

            if (!$recipients) {
                continue;
            }

            /** @var Notification $notification */
            $this->messageService->createNotificationMessages(
                $notification,
                $eventLog,
                $recipients,
//                $placeholders,
                $dt,
                $event,
                $entity,
                $context
            );
        }

        $this->em->clear();
    }

    /**
     * @param $entity
     * @return Team|null
     */
    protected function getEntityOwnerTeam($entity): ?Team
    {
        return ([
            User::class => static function (User $entity) {
                return $entity->getTeam();
            },
            Vehicle::class => static function (Vehicle $entity) {
                return $entity->getTeam();
            },
            Device::class => static function (Device $entity) {
                return $entity->getTeam();
            },
            Team::class => static function (Team $entity) {
                return $entity;
            },
            AreaHistory::class => static function (AreaHistory $entity) {
                return $entity->getVehicle()->getTeam();
            },
            Reminder::class => static function (Reminder $entity) {
                return $entity->getTeam();
            },
            ServiceRecord::class => static function (ServiceRecord $entity) {
                return $entity->getTeam();
            },
            Client::class => static function (Client $entity) {
                return $entity->getTeam();
            },
            TrackerHistory::class => static function (TrackerHistory $entity) {
                return !empty($entity->getVehicle())
                    ? $entity->getVehicle()->getTeam()
                    : $entity->getDevice()->getTeam();
            },
            TrackerHistoryIO::class => static function (TrackerHistoryIO $entity) {
                return !empty($entity->getVehicle())
                    ? $entity->getVehicle()->getTeam()
                    : $entity->getDevice()->getTeam();
            },
            TrackerHistorySensor::class => static function (TrackerHistorySensor $entity) {
                return !empty($entity->getVehicle())
                    ? $entity->getVehicle()->getTeam()
                    : $entity->getDevice()->getTeam();
            },
            Speeding::class => static function (Speeding $entity) {
                return $entity->getDevice()->getTeam();
            },
            Route::class => static function (Route $entity) {
                return $entity->getVehicle() ? $entity->getVehicle()->getTeam() : null;
            },
            Idling::class => static function (Idling $entity) {
                return $entity->getDevice()->getTeam();
            },
            DigitalFormAnswer::class => static function (DigitalFormAnswer $entity) {
                return $entity->getUser()->getTeam();
            },
            Document::class => static function (Document $entity) {
                return $entity->getTeam();
            },
            DocumentRecord::class => static function (DocumentRecord $entity) {
                return $entity->getTeam();
            },
            VehicleOdometer::class => static function (VehicleOdometer $entity) {
                return !empty($entity->getVehicle())
                    ? $entity->getVehicle()->getTeam()
                    : $entity->getDevice()->getTeam();
            },
            Asset::class => static function (Asset $entity) {
                return $entity->getTeam();
            },
            Invoice::class => static function (Invoice $entity) {
                return $entity?->getClient()->getTeam();
            },
        ][ClassUtils::getClass($entity)] ?? static function () {
            return null;
        })(
            $entity
        );
    }
}
