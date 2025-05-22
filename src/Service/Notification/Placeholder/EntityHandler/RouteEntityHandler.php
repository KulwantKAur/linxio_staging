<?php

namespace App\Service\Notification\Placeholder\EntityHandler;

use App\Entity\Notification\Template;
use App\Entity\Route;
use App\Entity\Team;
use App\Entity\User;
use App\Service\Notification\Placeholder\AbstractEntityHandlerDecorator;
use App\Util\DateHelper;
use App\Util\MetricHelper;

class RouteEntityHandler extends AbstractEntityHandlerDecorator
{
    /** @var Route */
    protected $entity;

    /**
     * @return Team|null
     */
    public function getTeam(): ?Team
    {
        return $this->entity->getVehicle() ? $this->entity->getVehicle()->getTeam() : null;
    }

    public function getValueHandlerPlaceholder(?User $user = null): array
    {
        return [
            'fromCompany' => $this->getFromCompany(),
            'regNo' => $this->entity->getVehicle() ? $this->entity->getVehicle()->getRegNo() : self::DEFAULT_UNKNOWN,
            'team' => $this->getTeamName(),
            'model' => $this->entity->getVehicle()
                ? $this->entity->getVehicle()->getModel() : self::DEFAULT_UNKNOWN,
            'startedAtTime' => $this->entity->getStartedAt()
                ? DateHelper::formatDate(
                    $this->entity->getStartedAt(),
                    $user?->getDateFormatSettingConverted(true)
                    ?? $this->entity->getVehicle()->getTeam()->getDateFormatSettingConverted(true)
                    ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                    $user?->getTimezone() ?? $this->entity->getTimeZoneName()
                )
                : self::DEFAULT_UNKNOWN,
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
            'driver' => $this->entity->getVehicle()
                ? ($this->entity->getVehicle()->getDriverName() ?? self::DEFAULT_UNKNOWN)
                : self::DEFAULT_UNKNOWN,
            'duration' => $this->entity->getDuration()
                ? DateHelper::seconds2human($this->entity->getDuration()) : null,
            'distance' => $this->entity->getDistance()
                ? MetricHelper::metersToHumanKm($this->entity->getDistance()) : null,
            'formTitle' => !empty($this->getContext()['form'])
                ? implode(",", $this->getContext()['form']) : self::DEFAULT_UNKNOWN,
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
}
