<?php

namespace App\Service\Notification\Placeholder\EntityHandler;

use App\Entity\EventLog\EventLog;
use App\Entity\Notification\Template;
use App\Entity\Team;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\User;
use App\Service\Notification\Placeholder\AbstractEntityHandlerDecorator;
use App\Util\DateHelper;
use App\Util\MetricHelper;

class TrackerHistoryEntityHandler extends AbstractEntityHandlerDecorator
{
    /** @var TrackerHistory */
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
            'fromCompany' => $this->getFromCompany(),
            'regNo' => $this->entity->getVehicle()?->getRegNo() ?? self::DEFAULT_UNKNOWN,
            'defaultLabel' => $this->entity->getVehicle()?->getDefaultLabel() ?? self::DEFAULT_UNKNOWN,
            'model' => $this->entity->getVehicle()?->getModel() ?? self::DEFAULT_UNKNOWN,
            'team' => $this->getTeamName(),
            'driver' => $this->getDriver(),
            'status' => $this->entity->getVehicle()?->getStatus(),
            'avgSpeed' => $this->entity->getSpeed() ?? null,
            'speedLimit' => $this->context['speedLimit'] ?? null,
            'lat' => $this->context['lat'] ?? null,
            'lng' => $this->context['lng'] ?? null,
            'address' => $this->context['address'] ?? null,
            'duration' => DateHelper::seconds2human($this->getContext()[EventLog::DURATION] ?? null)
                ?? self::DEFAULT_UNKNOWN,
            'distance' => MetricHelper::metersToHumanKm($this->getContext()[EventLog::DISTANCE] ?? null)
                ?? self::DEFAULT_UNKNOWN,
            'device' => $this->entity->getDevice()?->getImei(),
            'batteryVoltage' => $this->entity->getExternalVoltageVolts(),
            'batteryVoltagePercentage' => $this->entity->getBatteryVoltagePercentage(),
            'deviceImei' => $this->entity->getDevice()?->getImei(),
            'triggeredBy' => $this->getDriver() ?? self::DEFAULT_UNKNOWN,
            'tsTime' => $this->entity->getTs()
                ? DateHelper::formatDate(
                    $this->entity->getTs(),
                    $user?->getDateFormatSettingConverted(true)
                    ?? $this->entity->getTeam()->getDateFormatSettingConverted(true)
                    ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                    $user?->getTimezone() ?? $this->entity->getTimeZoneName()
                ) : null,
            'createdTime' => $this->entity->getCreatedAt()
                ? DateHelper::formatDate(
                    $this->entity->getCreatedAt(),
                    $user?->getDateFormatSettingConverted(true)
                    ?? $this->entity->getTeam()->getDateFormatSettingConverted(true)
                    ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                    $user?->getTimezone() ?? $this->entity->getTimeZoneName()
                ) : null,
            'regNoWithModel' => $this->getVehicleRegNoWithModel() ?? self::DEFAULT_UNKNOWN,
            'regNoWithModelOrDevice' =>
                $this->getVehicleRegNoWithModel($this->translator->trans('vehicle', [],
                        Template::TRANSLATE_DOMAIN) . ':')
                ?? $this->getDeviceImei($this->translator->trans('device', [], Template::TRANSLATE_DOMAIN) . ':')
                    ?? self::DEFAULT_UNKNOWN,
            'regNoOrDevice' =>
                $this->getVehicleRegNo($this->translator->trans('vehicle', [], Template::TRANSLATE_DOMAIN) . ':')
                ?? $this->getDeviceImei($this->translator->trans('device', [], Template::TRANSLATE_DOMAIN) . ':')
                    ?? null,
            'vehicleUrl' => $this->getVehicleFrontendLink(),
            'driverUrl' => $this->getDriverFrontendLink(),
            'googleUrl' => $this->getGoogleFrontendLink(),
        ];
    }

    /**
     * @return string|null
     */
    protected function getVehicleFrontendLink(): ?string
    {
        $vehicleId = $this->entity->getVehicle()?->getId();

        return $vehicleId
            ? vsprintf(
                'Vehicle page: %s/client/fleet/%d/specification',
                [$this->getAppFrontUrl(), $vehicleId]
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
            $this->entity->getVehicle()->getDriver()?->getId()
            )
            : null;

        return $driverId
            ? vsprintf(
                'Driver page: %s/client/drivers/%d/profile-info',
                [$this->getAppFrontUrl(), $driverId]
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
     * @param $addText
     * @return string|null
     */
    protected function getDeviceImei($addText = null)
    {
        if (!$this->entity->getVehicle() && $this->entity->getDevice()) {
            if ($addText) {
                return vsprintf('%s %s', [$addText, $this->entity->getDevice()->getImei()]);
            }
            return vsprintf('%s', [$this->entity->getDevice()->getImei()]);
        }

        return null;
    }

    /**
     * @param $addText
     * @return string
     */
    protected function getDriver($addText = null)
    {
        if ($this->entity->getVehicle() && $this->entity->getVehicle()->getDriverName()) {
            if ($addText) {
                return vsprintf('%s %s', [$addText, $this->entity->getVehicle()->getDriverName()]);
            }
            return vsprintf('%s', [$this->entity->getVehicle()->getDriverName()]);
        }

        return self::DEFAULT_UNKNOWN;
    }

    /**
     * @param $addText
     * @return string|null
     */
    protected function getVehicleRegNo($addText = null)
    {
        if ($this->entity->getVehicle() && $this->entity->getVehicle()->getRegNo()) {
            if ($addText) {
                return vsprintf('%s %s', [$addText, $this->entity->getVehicle()->getRegNo()]);
            }
            return vsprintf('%s', [$this->entity->getVehicle()->getRegNo()]);
        }

        return null;
    }

    /**
     * @return string|null
     */
    protected function getGoogleFrontendLink(): ?string
    {
        $lat = $this->entity->getLat() ?? $this->getContext()[EventLog::LAT] ?? null;
        $lng = $this->entity->getLng() ?? $this->getContext()[EventLog::LNG] ?? null;

        return ($lat && $lng)
            ? vsprintf(
                'Google maps link: %s/?q=%s,%s',
                [
                    self::GOOGLE_MAPS_LINK,
                    $lat,
                    $lng
                ]
            )
            : null;
    }
}
