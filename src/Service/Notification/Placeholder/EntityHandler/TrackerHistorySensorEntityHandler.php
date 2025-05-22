<?php

namespace App\Service\Notification\Placeholder\EntityHandler;

use App\Entity\Notification\Template;
use App\Entity\Team;
use App\Entity\Tracker\TrackerHistorySensor;
use App\Entity\User;
use App\Service\Notification\Placeholder\AbstractEntityHandlerDecorator;
use App\Util\DateHelper;

class TrackerHistorySensorEntityHandler extends AbstractEntityHandlerDecorator
{
    /** @var TrackerHistorySensor */
    protected $entity;

    /**
     * @return Team|null
     */
    public function getTeam(): ?Team
    {
        return !empty($this->entity->getVehicle())
            ? $this->entity->getVehicle()->getTeam()
            : $this->entity->getDevice()->getTeam();
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
            'sensorId' => $this->entity->getDeviceSensor() ? $this->entity->getDeviceSensor()->getSensorIdField()
                : self::DEFAULT_UNKNOWN,
            'sensorLabel' => ($this->entity->getDeviceSensor() && $this->entity->getDeviceSensor()->getLabel())
                ? '(' . $this->entity->getDeviceSensor()->getLabel() . ')'
                : null,
            'occurredAtTime' => DateHelper::formatDate(
                $this->entity->getOccurredAt(),
                $user?->getDateFormatSettingConverted(true)
                ?? $this->entity->getTeam()->getDateFormatSettingConverted(true)
                ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                $user?->getTimezone() ?? $this->entity->getVehicle()?->getTimeZoneName()
            ),
            'sensorTemperature' => $this->entity->getTemperature() ?? self::DEFAULT_UNKNOWN,
            'sensorHumidity' => $this->entity->getHumidity() ?? self::DEFAULT_UNKNOWN,
            'sensorLightStatus' => !is_null($this->entity->getLight())
                ? ($this->entity->toArray()['light'] ? $this->translator->trans('on', [],
                    Template::TRANSLATE_DOMAIN) : $this->translator->trans('off', [], Template::TRANSLATE_DOMAIN))
                : self::DEFAULT_UNKNOWN,
            'sensorBatteryLevel' => $this->entity->getBatteryPercentage() ?? self::DEFAULT_UNKNOWN,
            'sensorStatus' => !is_null($this->entity->getStatus())
                ? ($this->entity->getDeviceSensor()->getStatusText())
                : self::DEFAULT_UNKNOWN,
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
