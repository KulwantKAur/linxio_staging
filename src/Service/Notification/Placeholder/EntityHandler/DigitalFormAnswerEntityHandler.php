<?php

namespace App\Service\Notification\Placeholder\EntityHandler;

use App\Entity\DigitalFormAnswer;
use App\Entity\Notification\Template;
use App\Entity\Team;
use App\Entity\User;
use App\Service\Notification\Placeholder\AbstractEntityHandlerDecorator;
use App\Util\DateHelper;

class DigitalFormAnswerEntityHandler extends AbstractEntityHandlerDecorator
{
    /** @var DigitalFormAnswer */
    protected $entity;

    /**
     * @return Team
     */
    public function getTeam(): Team
    {
        return $this->entity->getUser()->getTeam();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getValueHandlerPlaceholder(?User $user = null): array
    {
        return [
            'fromCompany' => $this->getFromCompany(),
            'regNo' => $this->entity->getVehicle()
                ? $this->entity->getVehicle()->getRegNo() : self::DEFAULT_UNKNOWN,
            'createdTime' => DateHelper::formatDate(
                $this->entity->getCreatedAt(),
                $user?->getDateFormatSettingConverted(true)
                ?? $this->entity->getVehicle()?->getTeam()->getDateFormatSettingConverted(true)
                ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                $user?->getTimezone() ?? $this->entity->getVehicle()?->getTimeZoneName()
            ),
            'formTitle' => $this->entity->getDigitalForm()?->getTitle(),
            'driver' => $this->entity->getUser()?->getFullName(),
            'dataUrl' => $this->getFrontendLink(),
        ];
    }

    /**
     * @return string|null
     */
    protected function getFrontendLink(): ?string
    {

        return vsprintf(
            '%s: %s/client/reports/summary_details/vehicle_inspections/%d',
            [
                $this->translator->trans('form_page', [], Template::TRANSLATE_DOMAIN),
                $this->getAppFrontUrl(),
                $this->entity->getId()
            ]
        );
    }
}
