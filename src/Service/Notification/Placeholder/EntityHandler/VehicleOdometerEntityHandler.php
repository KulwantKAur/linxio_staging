<?php

namespace App\Service\Notification\Placeholder\EntityHandler;

use App\Entity\Team;
use App\Entity\User;
use App\Entity\VehicleOdometer;
use App\Service\Notification\Placeholder\AbstractEntityHandlerDecorator;
use App\Util\DateHelper;

class VehicleOdometerEntityHandler extends AbstractEntityHandlerDecorator
{
    /** @var VehicleOdometer */
    protected $entity;

    /**
     * @return Team|null
     */
    public function getTeam(): ?Team
    {
        return $this->entity->getVehicle()?->getTeam() ?? $this->entity->getDevice()?->getTeam();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getValueHandlerPlaceholder(?User $user = null): array
    {
        return [
            'fromCompany' => $this->getFromCompany(),
            'regNo' => $this->entity->getVehicle()
                ? $this->entity->getVehicle()->getRegNo() : self::DEFAULT_UNKNOWN,
            'createdTime' => DateHelper::formatDate(
                $this->entity->getCreatedAt(),
                $user?->getDateFormatSettingConverted(true)
                ?? $this->entity->getVehicle()->getTeam()->getDateFormatSettingConverted(true)
                ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                $user?->getTimezone() ?? $this->entity->getVehicle()?->getTimeZoneName()
            ),
            'createdBy' => $this->entity->getCreatedBy()->getFullName() ?? null,
            'newValue' => (int)($this->entity->getOdometer() / 1000) ?? null,
            'oldValue' => !empty($this->getContext()['oldValue'])
                ? (int)($this->getContext()['oldValue'] / 1000) : null,
        ];
    }

    /**
     * @return ?array
     */
    protected function getFrontendLink(): ?array
    {
        return null;
    }
}
