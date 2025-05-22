<?php

namespace App\Service\Notification\Placeholder\EntityHandler;

use App\Entity\Team;
use App\Entity\Tracker\TrackerAuthUnknown;
use App\Entity\User;
use App\Service\Notification\Placeholder\AbstractEntityHandlerDecorator;
use App\Util\DateHelper;

class TrackerAuthUnknownEntityHandler extends AbstractEntityHandlerDecorator
{
    /** @var TrackerAuthUnknown*/
    protected $entity;

    /**
     * @return Team|null
     */
    public function getTeam(): ?Team
    {
        return null;
    }

    public function getValueHandlerPlaceholder(?User $user = null): array
    {
        return [
            'fromCompany' => $this->getFromCompany(),
            'deviceImei' => $this->entity->getImei(),
            'status' => self::DEFAULT_UNKNOWN,
            'createdTime' => $this->entity->getCreatedAt()
                ? DateHelper::formatDate(
                    $this->entity->getCreatedAt(),
                    $user?->getDateFormatSettingConverted(true)
                        ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                    $user?->getTimezone() ?? $this->entity->getTimeZoneName()
                ) : null,
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
