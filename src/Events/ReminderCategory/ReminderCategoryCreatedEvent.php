<?php

namespace App\Events\ReminderCategory;

use App\Entity\ReminderCategory;
use Symfony\Contracts\EventDispatcher\Event;

class ReminderCategoryCreatedEvent extends Event
{
    const NAME = 'app.event.reminderCategory.created';
    protected $reminderCategory;

    public function __construct(ReminderCategory $reminderCategory)
    {
        $this->reminderCategory = $reminderCategory;
    }

    public function getReminderCategory(): ReminderCategory
    {
        return $this->reminderCategory;
    }
}