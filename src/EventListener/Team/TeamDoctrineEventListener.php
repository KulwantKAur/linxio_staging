<?php

namespace App\EventListener\Team;

use App\Service\Setting\SettingService;
use App\Entity\Team;
use Doctrine\ORM\Event\LifecycleEventArgs;

class TeamDoctrineEventListener
{
    private $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws \Exception
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        /** @var Team|object $object */
        $object = $args->getObject();

        if ($object instanceof Team && $object->getType() !== Team::TEAM_ADMIN) {
            $this->settingService->createDefaultTeamSettings($args->getObject());
        }
    }

}