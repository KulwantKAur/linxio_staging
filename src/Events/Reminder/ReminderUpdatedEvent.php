<?php

namespace App\Events\Reminder;

use App\Entity\Reminder;
use Symfony\Contracts\EventDispatcher\Event;

class ReminderUpdatedEvent extends Event
{
    const NAME = 'app.event.reminder.updated';
    protected $reminder;

    public function __construct(Reminder $reminder)
    {
        $this->reminder = $reminder;
    }

    public function getReminder(): Reminder
    {
        return $this->reminder;
    }
}