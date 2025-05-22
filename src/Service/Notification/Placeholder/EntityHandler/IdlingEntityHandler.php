<?php

namespace App\Service\Notification\Placeholder\EntityHandler;

use App\Entity\Idling;
use App\Entity\Notification\Template;
use App\Entity\Team;
use App\Entity\User;
use App\Service\Notification\Placeholder\AbstractEntityHandlerDecorator;
use App\Util\DateHelper;

class IdlingEntityHandler extends AbstractEntityHandlerDecorator
{
    /** @var Idling */
    protected $entity;

    /**
     * @return Team|null
     */
    public function getTeam(): ?Team
    {
        return $this->entity->getVehicle()?->getTeam();
    }

    public function getValueHandlerPlaceholder(?User $user = null): array
    {
        return [
            'fromCompany' => $this->getFromCompany(),
            'regNo' => $this->getVehicleRegNo() ?? null,
            'regNoOrDevice' => $this->getVehicleRegNo() ?? $this->getDeviceImei() ?? null,
            'startedTime' => $this->entity->getStartedAt()
                ? DateHelper::formatDate(
                    $this->entity->getStartedAt(),
                    $user?->getDateFormatSettingConverted(true)
                    ?? $this->entity->getVehicle()?->getTeam()->getDateFormatSettingConverted(true)
                    ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                    $user?->getTimezone() ?? $this->entity->getTimeZoneName()
                ) : null,
            'duration' => $this->entity->getDuration()
                ? DateHelper::seconds2human($this->entity->getDuration())
                : null,
            'regNoWithModel' => $this->getRegNoWithModel() ?? self::DEFAULT_UNKNOWN,
            'regNoWithModelOrDevice' => $this->getRegNoWithModel() ?? $this->getDeviceImei() ?? self::DEFAULT_UNKNOWN,
            'driver' => $this->getDriver(),
            'note' => (!$this->entity->getVehicle() && $this->entity->getDevice())
                ? $this->translator->trans('note_device_not_installed', [], Template::TRANSLATE_DOMAIN)
                : null,
            'vehicleUrl' => $this->getVehicleFrontendLink(),
            'driverUrl' => $this->getDriverFrontendLink(),
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
     * @return string|null
     */
    protected function getDriverFrontendLink(): ?string
    {
        $driverId = $this->entity->getVehicle()
            ? (
            $this->entity->getVehicle()->getDriver()
                ? $this->entity->getVehicle()->getDriver()->getId()
                : null
            )
            : null;

        return $driverId
            ? vsprintf(
                '%s: %s/client/drivers/%d/profile-info',
                [
                    $this->translator->trans('driver_page', [], Template::TRANSLATE_DOMAIN),
                    $this->getAppFrontUrl(),
                    $driverId
                ]
            )
            : null;
    }

    /**
     * @return string|null
     */
    protected function getRegNoWithModel()
    {
        if ($this->entity->getVehicle()) {
            if ($this->entity->getVehicle()->getModel()) {
                return vsprintf(
                    '%s %s (%s)',
                    [
                        $this->translator->trans('Vehicle', [], Template::TRANSLATE_DOMAIN),
                        $this->entity->getVehicle()->getRegNo(),
                        $this->entity->getVehicle()->getModel()
                    ]
                );
            } else {
                return vsprintf('%s %s', [
                    $this->translator->trans('Vehicle', [], Template::TRANSLATE_DOMAIN),
                    $this->entity->getVehicle()->getRegNo()
                ]);
            }
        }

        return null;
    }

    /**
     * @return string|null
     */
    protected function getDeviceImei()
    {
        if (!$this->entity->getVehicle() && $this->entity->getDevice()) {
            return vsprintf('%s: %s', [
                $this->translator->trans('Device', [], Template::TRANSLATE_DOMAIN),
                $this->entity->getDevice()->getImei()
            ]);
        }

        return null;
    }

    /**
     * @return string|null
     */
    protected function getVehicleRegNo()
    {
        if ($this->entity->getVehicle() && $this->entity->getVehicle()->getRegNo()) {
            return vsprintf('%s: %s', [
                $this->translator->trans('Vehicle', [], Template::TRANSLATE_DOMAIN),
                $this->entity->getVehicle()->getRegNo()
            ]);
        }

        return null;
    }

    /**
     * @return string|null
     */
    protected function getDriver()
    {
        if ($this->entity->getVehicle() && $this->entity->getVehicle()->getDriverName()) {
            return vsprintf(
                '%s %s',
                [
                    $this->translator->trans('with_driver', [], Template::TRANSLATE_DOMAIN),
                    $this->entity->getVehicle()->getDriverName()
                ]
            );
        }

        return $this->translator->trans('without_driver', [], Template::TRANSLATE_DOMAIN);
    }
}
