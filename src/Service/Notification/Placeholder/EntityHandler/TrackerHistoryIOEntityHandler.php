<?php

namespace App\Service\Notification\Placeholder\EntityHandler;

use App\Entity\Notification\Template;
use App\Entity\Team;
use App\Entity\Tracker\TrackerHistoryIO;
use App\Entity\User;
use App\Service\Notification\Placeholder\AbstractEntityHandlerDecorator;
use App\Util\DateHelper;

class TrackerHistoryIOEntityHandler extends AbstractEntityHandlerDecorator
{
    /** @var TrackerHistoryIO */
    protected $entity;

    /**
     * @return Team|null
     */
    public function getTeam(): ?Team
    {
        return $this->entity->getVehicle()
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
            'regNoWithModelOrDevice' => $this->getVehicleRegNoWithModel() ?? $this->entity->getDevice()?->getImei(),
            'fromCompany' => $this->getFromCompany(),
            'regNo' => $this->entity->getVehicle()
                ? (!empty($this->entity->getVehicle()->getRegNo())
                    ? vsprintf(
                        '%s (%s),',
                        [
                            $this->translator->trans('installed_in', [], Template::TRANSLATE_DOMAIN),
                            $this->entity->getVehicle()->getRegNo()
                        ]
                    ) : null)
                : self::DEFAULT_UNKNOWN,
            'model' => $this->entity->getVehicle()
                ? $this->entity->getVehicle()->getModel() : self::DEFAULT_UNKNOWN,
            'sensorStatus' => !is_null($this->entity->getStatusIO())
                ? (($this->entity->getStatusIO())
                    ? $this->translator->trans('on', [], Template::TRANSLATE_DOMAIN)
                    : $this->translator->trans('off', [], Template::TRANSLATE_DOMAIN))
                : self::DEFAULT_UNKNOWN,
            'sensorIOType' => !is_null($this->entity->getType())
                ? $this->entity->getType()->getName()
                : null,
            'deviceImei' => $this->entity->getDevice() ? $this->entity->getDevice()->getImei() : null,
            'deviceOrVehicle' => $this->entity->getVehicle()
                ? (
                !empty($this->entity->getVehicle()->getRegNo())
                    ? vsprintf('%s %s', ['vehicle', $this->entity->getVehicle()->getRegNo()])
                    : vsprintf('%s %s', [
                    $this->translator->trans('device', [], Template::TRANSLATE_DOMAIN),
                    $this->entity->getDevice()->getImei()
                ]))
                : vsprintf('%s %s', [
                    $this->translator->trans('device', [], Template::TRANSLATE_DOMAIN),
                    $this->entity->getDevice()->getImei()
                ]),
            'driver' => $this->entity->getVehicle()->getDriverName() ?? self::DEFAULT_UNKNOWN,
            'createdTime' => $this->entity->getCreatedAt()
                ? DateHelper::formatDate(
                    $this->entity->getCreatedAt(),
                    $user?->getDateFormatSettingConverted(true)
                    ?? $this->entity->getTeam()->getDateFormatSettingConverted(true)
                    ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                    $user?->getTimezone() ?? $this->entity->getTimeZoneName()
                ) : null,
            'eventTime' => $this->getEventTime()
                ? DateHelper::formatDate(
                    $this->getEventTime(),
                    $user?->getDateFormatSettingConverted(true)
                    ?? $this->entity->getTeam()->getDateFormatSettingConverted(true)
                    ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                    $user?->getTimezone() ?? $this->entity->getTimeZoneName()
                ) : null,
            'vehicleUrl' => $this->getVehicleFrontendLink(),
        ];
    }

    /**
     * @return string|null
     */
    protected function getVehicleFrontendLink(): ?string
    {
        $vehicleId = $this->entity->getVehicle() ? $this->entity->getVehicle()->getId() : null;

        return $vehicleId
            ? vsprintf(
                '%s: %s/client/fleet/%d/specification',
                [
                    $this->translator->trans('vehicle_page', [], Template::TRANSLATE_DOMAIN),
                    $this->getAppFrontUrl(),
                    $vehicleId
                ]
            )
            : null;
    }

    /**
     * @param $addText
     * @return string|null
     */
    protected function getVehicleRegNoWithModel($addText = null): ?string
    {
        if ($this->entity->getVehicle()) {
            return $this->entity->getVehicle()->getRegNoWithModel($addText);
        }

        return null;
    }

    /**
     * @return \DateTime|null
     */
    public function getEventTime()
    {
        if ($this->entity->getStatusIO() === 0) {
            return $this->entity?->getOccurredAtOff();
        }

        return $this->entity?->getOccurredAtOn();
    }
}
