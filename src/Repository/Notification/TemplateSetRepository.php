<?php

namespace App\Repository\Notification;

use App\Entity\Notification\TemplateSet;
use App\Entity\Setting;
use App\Entity\Team;
use \Doctrine\ORM\EntityRepository;

/**
 * Class TemplateSetRepository
 * @package App\Repository\Notification
 */
class TemplateSetRepository extends EntityRepository
{
    /**
     * @return TemplateSet|object|null
     */
    public function getDefault(): TemplateSet
    {
        return $this->findOneBy(['name' => TemplateSet::DEFAULT_TEMPLATE_SET_NAME]);
    }

    public function getByTeam(Team $team){
        $setting = $team->getSettingsByName(Setting::NOTIFICATION_TEMPLATE_SETTING);

        return (!$setting || !$setting->getValue()) ? $this->getDefault() : $this->find($setting->getValue());
    }
}
