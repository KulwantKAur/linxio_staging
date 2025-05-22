<?php

namespace App\EventListener\ElasticSearch;

use App\Entity\Client;
use App\Entity\DigitalFormSchedule;
use App\Service\DigitalForm\DigitalFormScheduleService;
use FOS\ElasticaBundle\Event\PostTransformEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClientListener implements EventSubscriberInterface
{
    /** @var DigitalFormScheduleService */
    private $scheduleService;


    public function __construct(
        DigitalFormScheduleService $scheduleService
    ) {
        $this->scheduleService = $scheduleService;
    }

    public static function getSubscribedEvents()
    {
        return array(
            PostTransformEvent::class => 'addCustomProperty'
        );
    }

    public function addCustomProperty(PostTransformEvent $event): void
    {
        $document = $event->getDocument();
        if ($event->getObject() instanceof Client) {
            $document->set('users_count', $event->getObject()->getUsersCount());
        }
        if ($event->getObject() instanceof DigitalFormSchedule) {
            $document->set('vehicleCount', $this->getVehicleCount($event->getObject()));
        }
    }

    private function getVehicleCount(DigitalFormSchedule $schedule)
    {
        $count = 0;
        foreach ($schedule->getDigitalFormScheduleRecipients() as $recipients) {
            $count = $this->scheduleService->getRecipientVehicleCount($recipients->getType(), $recipients->getValue());
        }

        return $count;
    }
}