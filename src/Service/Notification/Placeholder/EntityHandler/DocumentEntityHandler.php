<?php

namespace App\Service\Notification\Placeholder\EntityHandler;

use App\Entity\Document;
use App\Entity\Notification\Template;
use App\Entity\Team;
use App\Entity\User;
use App\Service\Notification\Placeholder\AbstractEntityHandlerDecorator;
use App\Util\DateHelper;

class DocumentEntityHandler extends AbstractEntityHandlerDecorator
{
    /** @var Document */
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
            'regNo' => $this->entity->getRegNo() ?? null,
            'model' => $this->entity->getVehicle()?->getModel(),
            'team' => $this->getTeamName(),
            'status' => $this->entity->getStatus() ?? null,
            'title' => $this->entity->getTitle() ?? null,
            'updateBy' => $this->entity->getUpdatedBy()->getName(),
            'updateTime' => $this->entity->getUpdatedAt()
                ? DateHelper::formatDate(
                    $this->entity->getUpdatedAt(),
                    $user?->getDateFormatSettingConverted(true)
                    ?? $this->entity->getTeam()->getDateFormatSettingConverted(true)
                    ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                    $user?->getTimezone() ?? $this->entity->getTimeZoneName()
                ) : null,
            'driver' => $this->entity->getVehicle()
                ? ($this->entity->getVehicle()->getDriverName() ?? self::DEFAULT_UNKNOWN)
                : self::DEFAULT_UNKNOWN,
            'dataUrl' => $this->getDocumentFrontendLink(),
            'dataByTypeDocument' => $this->getDataByTypeDocument(),
            'asset' => $this->entity->getAsset() ? $this->entity->getAsset()->getName() : self::DEFAULT_UNKNOWN,
        ];
    }

    /**
     * @return string|null
     */
    protected function getDataByTypeDocument(): ?string
    {
        switch ($this->entity->getDocumentType()) {
            case Document::DRIVER_DOCUMENT:
                $driverName = $this->entity->getVehicle()->getDriver()
                    ? $this->entity->getVehicle()->getDriver()->getFullName()
                    : self::DEFAULT_UNKNOWN;

                return sprintf('(driver - %s)', $driverName);
            case Document::VEHICLE_DOCUMENT:
                $vehicleRegNo = $this->entity->getVehicle()
                    ? $this->entity->getVehicle()->getRegNo()
                    : self::DEFAULT_UNKNOWN;

                return sprintf('(vehicle - %s)', $vehicleRegNo);
//            case Document::ASSET_DOCUMENT:
//                return null;
            default:
                return null;
        }
    }

    /**
     * @return string|null
     */
    protected function getDocumentFrontendLink(): ?string
    {
        $documentId = $this->entity->getId();

        switch ($this->entity->getDocumentType()) {
            case Document::DRIVER_DOCUMENT:
                $driverId = $this->entity->getVehicle()?->getDriver()?->getId();

                return ($driverId && $documentId)
                    ? vsprintf(
                        '%s: %s/client/drivers/%d/documents/%d',
                        [
                            $this->translator->trans('document_page', [], Template::TRANSLATE_DOMAIN),
                            $this->getAppFrontUrl(),
                            $driverId,
                            $documentId
                        ]
                    )
                    : null;
            case Document::VEHICLE_DOCUMENT:
                $vehicleId = $this->entity->getVehicle()?->getId();

                return ($vehicleId && $documentId)
                    ? vsprintf(
                        '%s: %s/client/fleet/%d/documents/%d',
                        [
                            $this->translator->trans('document_page', [], Template::TRANSLATE_DOMAIN),
                            $this->getAppFrontUrl(),
                            $vehicleId,
                            $documentId
                        ]
                    )
                    : null;
//            case Document::ASSET_DOCUMENT:
//                return null;
            default:
                return null;
        }
    }
}
