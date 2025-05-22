<?php

namespace App\Service\Notification\AttachmentHandler;

use App\Entity\EventLog\EventLog;

interface AttachmentInterface
{
    public function getAttachments(EventLog $eventLog): array;
}