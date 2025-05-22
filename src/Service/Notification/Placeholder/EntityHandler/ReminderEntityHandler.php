<?php

namespace App\Service\Notification\Placeholder\EntityHandler;

use App\Entity\Notification\Template;
use App\Entity\Reminder;
use App\Entity\Team;
use App\Entity\User;
use App\Service\Notification\Placeholder\AbstractEntityHandlerDecorator;
use App\Util\DateHelper;

class ReminderEntityHandler extends AbstractEntityHandlerDecorator
{
    /** @var Reminder */
    protected $entity;

    /**
     * @return Team
     */
    public function getTeam(): Team
    {
        return $this->entity->getTeam();
    }

    public function getValueHandlerPlaceholder(?User $user = null): array
    {
        return [
            'fromCompany' => $this->getFromCompany(),
            'regNo' => $this->entity->getVehicle()?->getRegNo(),
            'model' => $this->entity->getVehicle()?->getModel(),
            'team' => $this->getTeamName(),
            'status' => $this->entity->getStatus(),
            'title' => $this->entity->getTitle(),
            'expirationDate' => $this->entity->getDate()
                ? DateHelper::formatDate(
                    $this->entity->getDate(),
                    $user?->getDateFormatSettingConverted(true)
                    ?? $this->entity->getTeam()->getDateFormatSettingConverted(true)
                    ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                    $user?->getTimezone() ?? $this->entity->getTimeZoneName()
                ) : null,
            'updateTime' => $this->entity->getUpdatedAt()
                ? DateHelper::formatDate(
                    $this->entity->getUpdatedAt(),
                    $user?->getDateFormatSettingConverted(true)
                    ?? $this->entity->getTeam()->getDateFormatSettingConverted(true)
                    ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                    $user?->getTimezone() ?? $this->entity->getTimeZoneName()
                ) : null,
            'eventTime' => DateHelper::formatDate(
                new \DateTime(),
                $user?->getDateFormatSettingConverted(true)
                ?? $this->entity->getTeam()->getDateFormatSettingConverted(true)
                ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                $user?->getTimezone() ?? $this->entity->getTimeZoneName()
            ),
            'createdBy' => $this->entity->getCreatedBy()
                ? $this->entity->getCreatedBy()->getFullName() : self::DEFAULT_UNKNOWN,
            'createdTime' => $this->entity->getCreatedAt()
                ? DateHelper::formatDate(
                    $this->entity->getCreatedAt(),
                    $user?->getDateFormatSettingConverted(true)
                    ?? $this->entity->getTeam()->getDateFormatSettingConverted(true)
                    ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                    $user?->getTimezone() ?? $this->entity->getTimeZoneName()
                ) : self::DEFAULT_UNKNOWN,
            'entity' => $this->entity->isVehicleReminder()
                ? $this->entity->getVehicle()->getRegNo()
                : (
                ($this->entity->isAssetReminder() && $this->entity->getAsset())
                    ? $this->entity->getAsset()->getName() : null
                ),
            'dataUrl' => $this->getReminderFrontendLink(),
            'expireParameter' => $this->getExpirationParameter($user),
        ];
    }

    /**
     * @return string|null
     */
    protected function getReminderFrontendLink(): ?string
    {
        $reminderId = $this->entity->getId() ?? null;

        switch ($this->entity->getType()) {
            case Reminder::VEHICLE_TYPE:
                $vehicleId = $this->entity->getVehicle()?->getId();

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
            case Reminder::ASSET_TYPE:
                $assetId = $this->entity->getAsset()?->getId();

                return ($assetId && $reminderId)
                    ? vsprintf(
                        '%s: %s/client/asset/%d/service-reminders/%d',
                        [
                            $this->translator->trans('reminder_page', [], Template::TRANSLATE_DOMAIN),
                            $this->getAppFrontUrl(),
                            $assetId,
                            $reminderId
                        ]
                    )
                    : null;
            default:
                return null;
        }
    }

    private function getExpirationParameter(?User $user): ?string
    {
        if ($this->context['type'] === 'date') {
            return $this->entity->getDate()
                ? DateHelper::formatDate(
                    $this->entity->getDate(),
                    $user?->getDateFormatSettingConverted(true)
                    ?? $this->entity->getTeam()->getDateFormatSettingConverted(true)
                    ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                    $user?->getTimezone() ?? $this->entity->getTimeZoneName()
                ) : null;
        } elseif ($this->context['type'] === 'mileage') {
            return isset($this->context['mileage']) ? 'in ' . $this->context['mileage'] / 1000 . ' of kms' : null;
        } elseif ($this->context['type'] === 'engineHours') {
            return isset($this->context['engineHours']) ? 'in ' . $this->context['engineHours'] . ' hours' : null;
        }

        return null;
    }
}
