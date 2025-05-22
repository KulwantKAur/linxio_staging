<?php

namespace App\EventListener\ReminderCategory;

use App\Entity\ReminderCategory;
use Doctrine\ORM\Event\LifecycleEventArgs;

class ReminderCategoryEntityListener
{
    public function postLoad(ReminderCategory $reminderCategory, LifecycleEventArgs $args)
    {
    }
}