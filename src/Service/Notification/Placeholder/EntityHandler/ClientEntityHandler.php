<?php

namespace App\Service\Notification\Placeholder\EntityHandler;

use App\Entity\Client;
use App\Entity\Notification\Template;
use App\Entity\Team;
use App\Entity\User;
use App\Service\Notification\Placeholder\AbstractEntityHandlerDecorator;
use App\Util\DateHelper;

class ClientEntityHandler extends AbstractEntityHandlerDecorator
{
    /** @var Client */
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
            'clientName' => $this->entity->getName() ?? null,
            'team' => $this->getTeamName(),
            'createdBy' => $this->entity->getCreatedByName() ?? null,
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
            'dataUrl' => $this->getFrontendLink() ?? null,
            'integrationName' => $this->context['integrationName'] ?? null,
        ];
    }

    /**
     * @return string
     */
    protected function getFrontendLink(): string
    {
        if ($this->entity->getTeam()->isResellerTeam()) {
            return vsprintf(
                'Client page: %s/reseller/clients/%d',
                [$this->getAppFrontUrl(), $this->entity->getId()]
            );
        } else {
            return vsprintf(
                '%s: %s/admin/clients/%d',
                [
                    $this->translator->trans('client_page', [], Template::TRANSLATE_DOMAIN),
                    $this->getAppFrontUrl(),
                    $this->entity->getId()
                ]
            );
        }
    }
}
