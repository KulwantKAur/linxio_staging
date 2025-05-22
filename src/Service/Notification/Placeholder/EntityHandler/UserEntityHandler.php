<?php

namespace App\Service\Notification\Placeholder\EntityHandler;

use App\Entity\Team;
use App\Entity\User;
use App\Service\Notification\Placeholder\AbstractEntityHandlerDecorator;
use App\Util\DateHelper;

class UserEntityHandler extends AbstractEntityHandlerDecorator
{
    /** @var User */
    protected $entity;

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
            'userEmail' => $this->entity->getEmail(),
            'userName' => $this->entity->getFullName(),
            'team' => $this->getTeamName(),
            'createdTime' => $this->entity->getCreatedAt()
                ? DateHelper::formatDate(
                    $this->entity->getCreatedAt(),
                    $user?->getDateFormatSettingConverted(true)
                    ?? $this->entity->getTeam()->getDateFormatSettingConverted(true)
                    ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                    $user?->getTimezone() ?? $this->entity->getTeam()->getTimezone()?->getName()
                ) : null,
            'triggeredBy' => $this->entity->getUpdatedByName() ?? $this->entity->getCreatedByName(),
            'createdBy' => $this->entity->getCreatedByName(),
            'updateTime' => $this->entity->getUpdatedAt()
                ? DateHelper::formatDate(
                    $this->entity->getUpdatedAt(),
                    $user?->getDateFormatSettingConverted(true)
                    ?? $this->entity->getTeam()->getDateFormatSettingConverted(true)
                    ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                    $user?->getTimezone() ?? $this->entity->getTeam()->getTimezone()?->getName()
                ) : null,
            'lastLoggedTime' => $this->entity->getLastLoggedAt()
                ? DateHelper::formatDate(
                    $this->entity->getLastLoggedAt(),
                    $user?->getDateFormatSettingConverted(true)
                    ?? $this->entity->getTeam()->getDateFormatSettingConverted(true)
                    ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                    $user?->getTimezone() ?? $this->entity->getTeam()->getTimezone()?->getName()
                ) : null,
            'dataMessage' => $this->entity->getBlockingMessage(),
            // changed this params (context) !!
            'oldValue' => $this->getContext()['oldValue'] ?? null,
            'clientEmail' => $this->entity->getName(),
            'clientName' => $this->getClientName(),
            'dataUrl' => $this->getUserFrontendLink(),
            'eventTime' => DateHelper::formatDate(
                new \DateTime(),
                $user?->getDateFormatSettingConverted(true)
                ?? $this->entity->getTeam()->getDateFormatSettingConverted(true)
                ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                $user?->getTimezone() ?? $this->entity->getTeam()->getTimezone()?->getName()),
            'triggeredByContext' => $this->getContext()['triggeredBy'] ?? null,
            'oldRole' => isset($this->getContext()['oldRole'])
                ? $this->translator->trans('role.' . $this->getContext()['oldRole'], [], 'entities') : null,
            'newRole' => $this->translator->trans('role.' . $this->entity->getRole()?->getName(), [], 'entities'),
        ];
    }

    /**
     * @return string
     */
    protected function getUserFrontendLink(): string
    {
        if ($this->entity->getTeam()->isAdminTeam()) {
            return vsprintf(
                '%s/admin/team/users/%d',
                [$this->getAppFrontUrl(), $this->entity->getId()]
            );
        } elseif ($this->entity->getTeam()->isResellerTeam()) {
            return vsprintf(
                '%s/reseller/clients/%d/users/%d',
                [$this->getAppFrontUrl(), $this->entity->getTeam()->getId(), $this->entity->getId()]
            );
        } else {
            return vsprintf(
                '%s/admin/clients/%d/users/%d',
                [$this->getAppFrontUrl(), $this->entity->getTeam()->getId(), $this->entity->getId()]
            );
        }
    }

    /**
     * @return string|null
     */
    protected function getClientName(): ?string
    {
        if ($this->getTeam()->isClientTeam()) {
            return $this->entity->getClient()->getLegalName();
        } elseif ($this->getTeam()->isResellerTeam()) {
            return $this->entity->getReseller()->getLegalName();
        }

        return null;
    }
}
