<?php

namespace App\Service\Notification\Placeholder\EntityHandler;

use App\Entity\AreaHistory;
use App\Entity\Notification\Template;
use App\Entity\Team;
use App\Entity\User;
use App\Service\Notification\Placeholder\AbstractEntityHandlerDecorator;
use App\Util\DateHelper;

class AreaHistoryEntityHandler extends AbstractEntityHandlerDecorator
{
    /** @var AreaHistory */
    protected $entity;

    /**
     * @return Team
     */
    public function getTeam(): Team
    {
        return $this->entity->getVehicle()->getTeam();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getValueHandlerPlaceholder(?User $user = null): array
    {
        return [
            'fromCompany' => $this->getFromCompany() ?? null,
            'area' => $this->entity->getArea()->getName() ?? null,
            'regNo' => $this->entity->getVehicle()->getRegNo() ?? null,
            'model' => $this->entity->getVehicle()
                ? $this->entity->getVehicle()->getModel() : self::DEFAULT_UNKNOWN,
            'team' => $this->getTeamName(),
            'driver' => $this->entity->getVehicle()->getDriverName()
                ? $this->entity->getVehicle()->getDriverName()
                : self::DEFAULT_UNKNOWN,
            'avgSpeed' => $this->getContext()['speed'] ?? null,
            'status' => $this->entity->getVehicle()->getStatus() ?? null,
            'triggeredBy' => $this->entity->getVehicle()->getUpdatedByName() ?? null,
            'arrivedTime' => $this->entity->getArrived()
                ? DateHelper::formatDate(
                    $this->entity->getArrived(),
                    $user?->getDateFormatSettingConverted(true)
                    ?? $this->entity->getVehicle()?->getTeam()->getDateFormatSettingConverted(true)
                    ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                    $user?->getTimezone() ?? $this->entity->getVehicle()->getTimeZoneName()
                ) : null,
            'departedTime' => $this->entity->getDeparted()
                ? DateHelper::formatDate(
                    $this->entity->getDeparted(),
                    $user?->getDateFormatSettingConverted(true)
                    ?? $this->entity->getVehicle()?->getTeam()->getDateFormatSettingConverted(true)
                    ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                    $user?->getTimezone() ?? $this->entity->getVehicle()->getTimeZoneName()
                ) : null,
            'regNoWithModel' => $this->entity->getVehicle()
                ? (
                !empty($this->entity->getVehicle()->getModel())
                    ? vsprintf(
                    '%s (%s)',
                    [$this->entity->getVehicle()->getRegNo(), $this->entity->getVehicle()->getModel()]
                )
                    : vsprintf('%s', [$this->entity->getVehicle()->getRegNo()])
                )
                : self::DEFAULT_UNKNOWN,
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
            ) : null;
    }

    /**
     * @return string|null
     */
    protected function getDriverFrontendLink(): ?string
    {
        $driverId = $this->entity->getVehicle()
            ? ($this->entity->getVehicle()->getDriver() ? $this->entity->getVehicle()->getDriver()->getId() : null)
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
}
