<?php

namespace App\Service\Notification;

use App\Entity\Notification\Transport;
use App\Entity\Notification\Notification;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;

class ImportanceService
{
    public const START_BUSINESS_HOUR = 8;
    public const END_BUSINESS_HOUR = 16;

    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param Notification $notification
     * @param Transport $transport
     * @return \DateTime
     */
    public function calculateSendTime(Notification $notification, Transport $transport): \DateTime
    {
        $strategy = $this->getImportanceStrategy($notification, $transport->getAlias());

        return $strategy();
    }

    private function getBusinessHours($timezone): array
    {

        $from = Carbon::now('UTC')->setTimezone($timezone)->setTime(self::START_BUSINESS_HOUR, 0)->setTimezone('UTC');
        $until = Carbon::now('UTC')->setTimezone($timezone)->setTime(self::END_BUSINESS_HOUR, 0)->setTimezone('UTC');

        if ($until < $from) {
            $until->addDay();
        }

        return [$from, $until];
    }

    private function getCustomHours(string $timezone, string $from, string $until): array
    {
        $from = Carbon::now('UTC')->setTimezone($timezone)->setTimeFromTimeString($from)->setTimezone('UTC');
        $until = Carbon::now('UTC')->setTimezone($timezone)->setTimeFromTimeString($until)->setTimezone('UTC');

        if ($until < $from) {
            $until->addDay();
        }

        return [$from, $until];
    }

    private function getImportanceStrategy(Notification $notification, string $transport): \Closure
    {
        $timezone = $notification->getClientTimezone();
        $nowHandler = static function () {
            return Carbon::now('UTC');
        };

        $businessHoursHandler = function () use ($timezone) {
            $now = Carbon::now('UTC');

            [$startBh, $endBh] = $this->getBusinessHours($timezone);

            if ($now->getTimestamp() < $startBh->getTimestamp()) {
                return $startBh;
            }

            if ($startBh->getTimestamp() < $now->getTimestamp() && $endBh->getTimestamp() > $now->getTimestamp()) {
                return $now;
            }

            return $startBh->modify('+1 day');
        };

        $customHandler = function () use ($notification, $timezone) {
            $now = Carbon::now('UTC');
            [$startBh, $endBh] = $this->getCustomHours(
                $timezone, $notification->getSendTimeFrom(), $notification->getSendTimeUntil()
            );

            if ($now->getTimestamp() < $startBh->getTimestamp()) {
                return $startBh;
            }

            if ($startBh->getTimestamp() < $now->getTimestamp() && $endBh->getTimestamp() > $now->getTimestamp()) {
                return $now;
            }

            return $startBh->modify('+1 day');
        };

        return [
            Transport::TRANSPORT_SMS => [
                Notification::TYPE_IMPORTANCE_IMMEDIATELY => $nowHandler,
                Notification::TYPE_IMPORTANCE_BUSINESS_HOURS => $businessHoursHandler,
                Notification::TYPE_IMPORTANCE_CUSTOM => $customHandler,
            ],
            Transport::TRANSPORT_EMAIL => [
                Notification::TYPE_IMPORTANCE_IMMEDIATELY => $nowHandler,
                Notification::TYPE_IMPORTANCE_BUSINESS_HOURS => $businessHoursHandler,
                Notification::TYPE_IMPORTANCE_CUSTOM => $customHandler,
            ],
            Transport::TRANSPORT_WEB_APP => [
                Notification::TYPE_IMPORTANCE_IMMEDIATELY => $nowHandler,
                Notification::TYPE_IMPORTANCE_BUSINESS_HOURS => $businessHoursHandler,
                Notification::TYPE_IMPORTANCE_CUSTOM => $customHandler,
            ],
            Transport::TRANSPORT_MOBILE_APP => [
                Notification::TYPE_IMPORTANCE_IMMEDIATELY => $nowHandler,
                Notification::TYPE_IMPORTANCE_BUSINESS_HOURS => $businessHoursHandler,
                Notification::TYPE_IMPORTANCE_CUSTOM => $customHandler,
            ],
        ][$transport][$notification->getImportance()] ?? $nowHandler;
    }
}
