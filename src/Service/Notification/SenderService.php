<?php

namespace App\Service\Notification;

use App\Entity\Notification\Transport;
use App\Entity\Team;

class SenderService
{
    public static function getSenderByTeamAndTransport(Team $team, Transport $transport): ?string
    {
        switch ($transport->getAlias()) {
            case Transport::TRANSPORT_EMAIL:
                return $team->getNotificationEmail();
            case Transport::TRANSPORT_SMS:
                return $team->getSmsName();
        }

        return null;
    }
}