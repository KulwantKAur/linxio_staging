<?php

namespace App\Service\Notification\Placeholder\EntityHandler;

use App\Entity\Notification\Template;
use App\Entity\ServiceRecord;
use App\Entity\Team;
use App\Entity\User;
use App\Service\Notification\Placeholder\AbstractEntityHandlerDecorator;
use App\Util\DateHelper;

class ServiceRecordEntityHandler extends AbstractEntityHandlerDecorator
{
    /** @var ServiceRecord */
    protected $entity;

    /**
     * @return Team|null
     * @throws \Exception
     */
    public function getTeam(): ?Team
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
            'regNoByReminder' => ($this->entity->getReminder() && $this->entity->getReminder()->getVehicle())
                ? $this->entity->getReminder()->getVehicle()->getRegNo() : self::DEFAULT_UNKNOWN,
            'modelByReminder' => ($this->entity->getReminder() && $this->entity->getReminder()->getVehicle())
                ? $this->entity->getReminder()->getVehicle()->getModel() : self::DEFAULT_UNKNOWN,
            'titleByReminder' => ($this->entity->getReminder() && $this->entity->getReminder()->getTitle())
                ? $this->entity->getReminder()->getTitle() : null,
            'team' => $this->getTeamName(),
            'status' => $this->entity->getStatus() ?? null,
            'regNoByRepair' => $this->entity->getRepairVehicle()
                ? $this->entity->getRepairVehicle()->getRegNo() : self::DEFAULT_UNKNOWN,
            'modelByRepair' => $this->entity->getRepairVehicle()
                ? $this->entity->getRepairVehicle()->getModel() : self::DEFAULT_UNKNOWN,
            'titleByRepair' => ($this->entity->getRepairData() && $this->entity->getRepairData()->getTitle())
                ? $this->entity->getRepairData()->getTitle() : null,
            'updateTime' => $this->entity->getUpdatedAt()
                ? DateHelper::formatDate(
                    $this->entity->getUpdatedAt(),
                    $user?->getDateFormatSettingConverted(true)
                    ?? $this->entity->getCreatedBy()?->getDateFormatSettingConverted(true)
                    ?? $this->entity->getTeam()->getDateFormatSettingConverted(true)
                    ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                    $user?->getTimezone() ?? $this->entity->getTimeZoneName()
                ) : self::DEFAULT_UNKNOWN,
            'createdBy' => $this->entity->getCreatedBy()
                ? $this->entity->getCreatedBy()->getFullName() : self::DEFAULT_UNKNOWN,
            'dataUrl' => $this->getServiceRecordFrontendLink(),
            'entity' => $this->entity->getEntityString(),
            'asset' => $this->entity->getRepairAsset() ? $this->entity->getRepairAsset()->getName() : null,
        ];
    }

    /**
     * @return string|null
     * @throws \Exception
     */
    protected function getServiceRecordFrontendLink(): ?string
    {
        switch ($this->entity->getType()) {
            case ServiceRecord::TYPE_SERVICE_RECORD:
                $reminderId = $this->entity->getReminderId() ?? null;
                $vehicleId = $this->entity->getServiceRecordVehicle()
                    ? $this->entity->getServiceRecordVehicle()->getId() : null;

                return ($vehicleId && $reminderId)
                    ? vsprintf(
                        '%s: %s/client/fleet/%d/service-reminders/%d',
                        [
                            $this->translator->trans('reminder_page', [], Template::TRANSLATE_DOMAIN),
                            $this->getAppFrontUrl(),
                            $vehicleId,
                            $reminderId
                        ]
                    )
                    : null;
            case ServiceRecord::TYPE_REPAIR:
                $serviceRepairId = $this->entity->getId();
                $vehicleId = $this->entity->getRepairVehicle() ? $this->entity->getRepairVehicle()->getId() : null;

                return ($vehicleId && $serviceRepairId)
                    ? vsprintf(
                        '%s: %s/client/fleet/%d/repair-costs/%d',
                        [
                            $this->translator->trans('repair_page', [], Template::TRANSLATE_DOMAIN),
                            $this->getAppFrontUrl(),
                            $vehicleId,
                            $serviceRepairId
                        ]
                    )
                    : null;
            case ServiceRecord::TYPE_ASSET_REPAIR:
                $serviceRepairId = $this->entity->getId();
                $assetId = $this->entity->getRepairAsset() ? $this->entity->getRepairAsset()->getId() : null;

                return ($assetId && $serviceRepairId)
                    ? vsprintf(
                        '%s: %s/client/asset/%d/repair-costs/%d',
                        [
                            $this->translator->trans('repair_page', [], Template::TRANSLATE_DOMAIN),
                            $this->appFrontUrl,
                            $assetId,
                            $serviceRepairId
                        ]
                    )
                    : null;
            default:
                return null;
        }
    }
}
