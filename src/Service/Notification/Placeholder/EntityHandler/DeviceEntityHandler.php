<?php

namespace App\Service\Notification\Placeholder\EntityHandler;

use App\Entity\Device;
use App\Entity\Team;
use App\Entity\User;
use App\Service\Notification\Placeholder\AbstractEntityHandlerDecorator;
use App\Util\DateHelper;

class DeviceEntityHandler extends AbstractEntityHandlerDecorator
{
    /** @var Device */
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
            'deviceImei' => $this->entity->getImei(),
            'status' => $this->entity->getStatus(),
            'createdTime' => $this->entity->getCreatedAt()
                ? DateHelper::formatDate(
                    $this->entity->getCreatedAt(),
                    $user?->getDateFormatSettingConverted(true)
                    ?? $this->entity->getTeam()->getDateFormatSettingConverted(true)
                    ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                    $user?->getTimezone() ?? $this->entity->getTimeZoneName()
                ) : null,
            'createdBy' => $this->entity->getCreatedBy()
                ? $this->entity->getCreatedBy()->getFullName() : self::DEFAULT_UNKNOWN,
            'updateTime' => $this->entity->getUpdatedAt()
                ? DateHelper::formatDate(
                    $this->entity->getUpdatedAt(),
                    $user?->getDateFormatSettingConverted(true)
                    ?? $this->entity->getTeam()->getDateFormatSettingConverted(true)
                    ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                    $user?->getTimezone() ?? $this->entity->getTimeZoneName()
                ) : null,
            'updateBy' => $this->entity->getUpdatedBy()
                ? $this->entity->getUpdatedBy()->getFullName() : self::DEFAULT_UNKNOWN,
            'dataUrl' => $this->getFrontendLink(),
            'imeiArray' => $this->context['imei_array'] ?? null,
            'count' => $this->context['count'] ?? null,
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
