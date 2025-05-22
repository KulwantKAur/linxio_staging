<?php

namespace App\Events\Reminder;

use App\Entity\Vehicle;
use Symfony\Contracts\EventDispatcher\Event;

class ReminderEsRefreshEvent extends Event
{
    const NAME = 'app.event.reminder.es.refresh';
    protected Vehicle $vehicle;

    public function __construct(Vehicle $vehicle)
    {
        $this->vehicle = $vehicle;
    }

//    public function getReminder(): Reminder
//    {
//        return $this->reminder;
//    }

    public function getVehicle(): Vehicle
    {
        return $this->vehicle;
    }
}