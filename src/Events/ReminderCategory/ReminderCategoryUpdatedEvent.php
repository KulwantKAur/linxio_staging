<?php

namespace App\Events\ReminderCategory;

use App\Entity\ReminderCategory;
use Symfony\Contracts\EventDispatcher\Event;

class ReminderCategoryUpdatedEvent extends Event
{
    const NAME = 'app.event.reminderCategory.updated';
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