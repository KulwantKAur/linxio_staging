<?php

namespace App\Service\Notification;

use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use App\Entity\Notification\NotificationTransports;
use App\Entity\Notification\Transport;
use App\Entity\Setting;
use App\Entity\Team;
use App\Entity\User;
use Doctrine\ORM\EntityManager;

class TransportService
{
    public const TRANSPORT_SELECT_OPTIONS = [
        [
            'name' => 'Sms',
            'alias' => Setting::SMS_SETTING,
        ],
        [
            'name' => 'E-Mail',
            'alias' => Setting::EMAIL_SETTING,
        ],
        [
            'name' => 'In App',
            'alias' => Setting::IN_APP_SETTING,
        ],
    ];

    private $settingRepository;

    public function __construct(EntityManager $em)
    {
        $this->settingRepository = $em->getRepository(Setting::class);
    }

    /**
     * @param User $user
     * @return array
     */
    public function getTransportsOptions(User $user): array
    {
        return \array_map(
            function ($item) use ($user) {
                /** @var Setting|null $setting */
                $setting = $user->getSettingByName($item['alias']);

                return \array_merge($item, ['active' => null === $setting ? false : (bool)$setting->getValue()]);
            },
            self::TRANSPORT_SELECT_OPTIONS
        );
    }

    /**
     * @param array $transports
     * @param Team $team
     * @return array
     */
    public function filterTransports(array $transports, Team $team): array
    {
        return \array_filter(
            $transports,
            function (Transport $t) use ($team) {
                $settingName = Transport::TRANSPORT_TYPE_TO_SETTING[$t->getAlias()];
                /** @var Setting|null $setting */
                $setting = $team->getSettingsByName($settingName);

                return null === $setting ? false : (bool)$setting->getValue();
            },
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * @param Notification $notification
     * @return array|Transport[]
     */
    public function getNotificationTransports(Notification $notification): array
    {
        $allowedTransports = $notification
            ->getTransports()
            ->map(
                static function (NotificationTransports $v) {
                    return $v->getTransport();
                }
            )->toArray();

        if (Event::TYPE_USER === $notification->getEvent()->getType()) {
            $allowedTransports = $this->filterTransports($allowedTransports, $notification->getOwnerTeam());
        }

        return $allowedTransports;
    }
}
