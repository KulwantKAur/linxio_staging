<?php

namespace App\Service\Notification\Placeholder\EntityHandler;

use App\Entity\Notification\Template;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Service\Notification\Placeholder\AbstractEntityHandlerDecorator;
use App\Util\DateHelper;

class VehicleEntityHandler extends AbstractEntityHandlerDecorator
{
    /** @var Vehicle */
    protected $entity;

    /**
     * @return Team
     */
    public function getTeam(): Team
    {
        return $this->entity->getTeam();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getValueHandlerPlaceholder(?User $user = null): array
    {
        return [
            'fromCompany' => $this->getFromCompany(),
            'regNo' => $this->entity->getRegNo() ?? self::DEFAULT_UNKNOWN,
            'model' => $this->entity->getModel() ?? self::DEFAULT_UNKNOWN,
            'team' => $this->getTeamName(),
            'createdBy' => $this->entity->getCreatedByName(),
            'createdTime' => $this->entity->getCreatedAt()
                ? DateHelper::formatDate(
                    $this->entity->getCreatedAt(),
                    $user?->getDateFormatSettingConverted(true)
                    ?? $this->entity->getTeam()->getDateFormatSettingConverted(true)
                    ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                    $user?->getTimezone() ?? $this->entity->getTimeZoneName()
                ) : null,
            'updateBy' => $this->entity->getUpdatedByName() ?? null,
            'updateTime' => $this->entity->getUpdatedAt()
                ? DateHelper::formatDate(
                    $this->entity->getUpdatedAt(),
                    $user?->getDateFormatSettingConverted(true)
                    ?? $this->entity->getTeam()->getDateFormatSettingConverted(true)
                    ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                    $user?->getTimezone() ?? $this->entity->getTimeZoneName()
                ) : null,
            'regNoWithModel' => $this->entity->getId()
                ? (!empty($this->entity->getModel())
                    ? vsprintf(
                        '%s (%s)',
                        [$this->entity->getRegNo(), $this->entity->getModel()]
                    )
                    : vsprintf('%s', [$this->entity->getRegNo()])
                )
                : self::DEFAULT_UNKNOWN,
            'status' => $this->entity->getStatus() ?? self::DEFAULT_UNKNOWN,
            'driver' => $this->entity->getDriverName() ?? self::DEFAULT_UNKNOWN,
            'oldValue' => $this->getContext()['oldValue'] ?? self::DEFAULT_UNKNOWN,
            'oldValueDriver' => !is_null($this->getContext()['oldValue'] ?? null)
                ? vsprintf('%s "%s"', [
                    $this->translator->trans('from_driver', [], Template::TRANSLATE_DOMAIN),
                    $this->getContext()['oldValue']
                ])
                : null,
            'vehicleUrl' => $this->getVehicleFrontendLink(),
            'driverUrl' => $this->getDriverFrontendLink(),
            'eventTime' => DateHelper::formatDate(
                new \DateTime(),
                $user?->getDateFormatSettingConverted(true)
                ?? $this->entity->getTeam()->getDateFormatSettingConverted(true)
                ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                $user?->getTimezone() ?? $this->entity->getTimeZoneName()
            ),
        ];
    }

    /**
     * @return string|null
     */
    protected function getVehicleFrontendLink(): ?string
    {
        $vehicleId = $this->entity->getId() ? $this->entity->getId() : null;

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
        $driverId = $this->entity->getDriver() ? $this->entity->getDriver()->getId() : null;

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
