<?php

namespace App\Service\Notification;

use App\Entity\Notification\Alert\AlertSetting;
use App\Entity\Notification\Alert\AlertSubType;
use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use App\Entity\Setting;
use App\Entity\Team;
use App\Entity\User;
use App\Service\BaseService;
use App\Service\ElasticSearch\ElasticSearch;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Contracts\Translation\TranslatorInterface;

class AlertService extends BaseService
{
    protected $translator;
    private $alertSettingRepository;
    private $em;
    private $alertSettingFinder;

    public const ELASTIC_NESTED_FIELDS = [];
    public const ELASTIC_SIMPLE_FIELDS = [
        'alertSubType' => 'alertSubType',
        'events' => 'events',
        'team' => 'alertSetting.team',
    ];
    public const ELASTIC_RANGE_FIELDS = [];

    /**
     * FuelIgnoreListService constructor.
     * @param TranslatorInterface $translator
     * @param EntityManager $em
     * @param TransformedFinder $alertSettingFinder
     */
    public function __construct(
        TranslatorInterface $translator,
        EntityManager $em,
        TransformedFinder $alertSettingFinder
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->alertSettingRepository = $em->getRepository(AlertSetting::class);
        $this->alertSettingFinder = new ElasticSearch($alertSettingFinder);
    }

    /**
     * @param array $params
     * @param User $user
     * @param bool $paginated
     * @return array
     */
    public function getAlertSetting(array $params, User $user, bool $paginated = true): array
    {
        if ($user->isInClientTeam()) {
            $params['team'] = [$user->getTeam()->getType()];
            $params['planId'] = $user->getPlan()->getId();
        } elseif ($user->isInResellerTeam()) {
            $params['team'] = [Team::TEAM_RESELLER, Team::TEAM_CLIENT];
        }
        $teams = $params['team'] ?? [Team::TEAM_ADMIN, Team::TEAM_CLIENT];
        $settings = [];
        foreach ($teams as $team) {
            $params['team'] = $team;
            $params['fields'] = array_merge(AlertSubType::DISPLAYED_VALUES, $params['fields'] ?? []);
            $fields = $this->prepareElasticFields($params);
            $result = $this->alertSettingFinder->find($fields, $fields['_source'] ?? [], $paginated);
            $settings[] = $this->prepareAlertData($result, $team, $user);
        }

        return $settings;
    }

    /**
     * @param $data
     * @param string $team
     * @param User $currentUser
     * @return mixed
     */
    public function prepareAlertData($data, string $team, User $currentUser)
    {
        $permissions = AlertSubType::PERMISSIONS_BY_ALERT_SUB_TYPE;
        $result['type'] = $team;
        $result['alertSubTypes'] = array_map(
            function (AlertSubType $alertSubType) use ($permissions, $currentUser) {
                $alertSubTypes = $alertSubType->toArray(['id', 'name', 'events'], $currentUser);
                $alertSubTypes['name'] = $this->translator->trans(
                    'alertSubType.' . $alertSubTypes['name'], [], 'entities'
                );

                if (isset($alertSubTypes['events']) && !empty($alertSubTypes['events'])) {
                    $alertSubTypes['events'] = array_values(array_filter(
                        $alertSubTypes['events'],
                        function ($event) use ($currentUser) {
                            return $this->filterEventBySetting($event['name'], $currentUser);
                        }
                    ));
                    $alertSubTypes['events'] = array_map(
                        function ($event) use ($alertSubType, $currentUser) {
                            $event['teamType'] = $alertSubType->getTeam() ? $alertSubType->getTeam() : null;
                            $event['alias'] = $this->translator->trans('event.' . $event['name'], [], 'entities');
                            $event['notificationCount'] = $this->getNotificationCountByUser(
                                $event['notifications'],
                                $currentUser
                            );

                            unset($event['notifications']);
                            return $event;
                        },
                        $alertSubTypes['events']
                    );
                } else {
                    return null;
                }

                if ($permissions[$alertSubTypes['name']] ?? null) {
                    if (!$currentUser->hasPermission($permissions[$alertSubTypes['name']])) {
                        return null;
                    }
                    $alertSubTypes['permissions'] = $permissions[$alertSubTypes['name']] ?? null;
                }

                return $alertSubTypes;
            },
            $data
        );

        $result['alertSubTypes'] = array_values(array_filter($result['alertSubTypes'], function ($item) {
            return !is_null($item);
        }));

        return $result;
    }

    /**
     * @param Notification[] $notifications
     * @param User $currentUser
     * @return mixed
     */
    public function getNotificationCountByUser($notifications, User $currentUser)
    {
        return $notifications->filter(
            static function (Notification $v) use ($currentUser) {
                return $v->getOwnerTeam() && ($v->getOwnerTeam()->getId() === $currentUser->getTeamId());
            }
        )->count();
    }

    public function filterEventBySetting(string $event, User $user)
    {
        if ($event === Event::EXCEEDING_SPEED_LIMIT) {
            $settingValue = $user->getSettingByName(Setting::BILLABLE_ADDONS)?->getValue();

            return $settingValue
                && (in_array(Setting::BILLABLE_ADDONS_SIGN_POST_SPEED_DATA, $settingValue)
                    || in_array(Setting::BILLABLE_ADDONS_SNAP_TO_ROADS, $settingValue)
                );
        }

        return true;
    }
}
