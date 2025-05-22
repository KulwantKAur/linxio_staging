<?php

namespace App\Service\Notification\Placeholder\EntityHandler;

use App\Entity\Document;
use App\Entity\DocumentRecord;
use App\Entity\Notification\Template;
use App\Entity\Team;
use App\Entity\User;
use App\Service\Notification\Placeholder\AbstractEntityHandlerDecorator;
use App\Util\DateHelper;

class DocumentRecordEntityHandler extends AbstractEntityHandlerDecorator
{
    /** @var DocumentRecord */
    protected $entity;


    /**
     * @return Team|null
     */
    public function getTeam(): ?Team
    {
        return $this->entity->getTeam() ?? null;
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
            'model' => $this->entity->getVehicle()
                ? $this->entity->getVehicle()->getModel() : self::DEFAULT_UNKNOWN,
            'team' => $this->getTeamName(),
            'title' => $this->entity->getDocument() ? $this->entity->getDocument()->getTitle() : self::DEFAULT_UNKNOWN,
            'status' => $this->entity->getDocument()->getStatus(),
            'updateBy' => $this->entity->getUpdatedBy()
                ? $this->entity->getUpdatedBy()->getName() : self::DEFAULT_UNKNOWN,
            'updateTime' => $this->entity->getUpdatedAt()
                ? DateHelper::formatDate(
                    $this->entity->getUpdatedAt(),
                    $user?->getDateFormatSettingConverted(true)
                    ?? $this->entity->getTeam()->getDateFormatSettingConverted(true)
                    ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                    $user?->getTimezone() ?? $this->entity->getTimeZoneName()
                ) : self::DEFAULT_UNKNOWN,
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
            'expirationDate' => $this->entity->getExpDate()
                ? DateHelper::formatDate(
                    $this->entity->getExpDate(),
                    $user?->getDateFormatSettingConverted(true)
                    ?? $this->entity->getTeam()->getDateFormatSettingConverted(true)
                    ?? DateHelper::NTF_DEFAULT_FORMAT_DATE,
                    $user?->getTimezone() ?? $this->entity->getTimeZoneName()
                ) : self::DEFAULT_UNKNOWN,
            'driver' => $this->entity->getDocument()->getDriver()
                ? $this->entity->getDocument()->getDriver()->getFullName()
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
        switch ($this->entity->getDocument()->getDocumentType()) {
            case Document::DRIVER_DOCUMENT:
                $driverName = $this->entity->getDriver()
                    ? $this->entity->getDriver()->getFullName() : self::DEFAULT_UNKNOWN;

                return sprintf('(%s - %s)',
                    $this->translator->trans('driver', [], Template::TRANSLATE_DOMAIN), $driverName);
            case Document::VEHICLE_DOCUMENT:
                $vehicleRegNo = $this->entity->getVehicle() ? $this->entity->getVehicle()->getRegNo() : null;

                return sprintf('(%s - %s)',
                    $this->translator->trans('vehicle', [], Template::TRANSLATE_DOMAIN), $vehicleRegNo);
            case Document::ASSET_DOCUMENT:
                $assetName = $this->entity->getAsset() ? $this->entity->getAsset()->getName() : null;

                return sprintf('(%s - %s)',
                    $this->translator->trans('asset', [], Template::TRANSLATE_DOMAIN), $assetName);
            default:
                return null;
        }
    }

    /**
     * @return string|null
     */
    protected function getDocumentFrontendLink(): ?string
    {
        $documentId = $this->entity->getDocument()->getId();

        switch ($this->entity->getDocument()->getDocumentType()) {
            case Document::DRIVER_DOCUMENT:
                $driverId = $this->entity->getDriver() ? $this->entity->getDriver()->getId() : null;

                return ($driverId && $documentId)
                    ? vsprintf(
                        '%s: %s/client/drivers/%d/documents/%d',
                        [
                            $this->translator->trans('document_page', [], Template::TRANSLATE_DOMAIN),
                            $this->appFrontUrl,
                            $driverId,
                            $documentId
                        ]
                    )
                    : null;
            case Document::VEHICLE_DOCUMENT:
                $vehicleId = $this->entity->getVehicle() ? $this->entity->getVehicle()->getId() : null;

                return ($vehicleId && $documentId)
                    ? vsprintf(
                        '%s: %s/client/fleet/%d/documents/%d',
                        [
                            $this->translator->trans('document_page', [], Template::TRANSLATE_DOMAIN),
                            $this->appFrontUrl,
                            $vehicleId,
                            $documentId
                        ]
                    )
                    : null;
            case Document::ASSET_DOCUMENT:
                $assetId = $this->entity->getAsset() ? $this->entity->getAsset()->getId() : null;

                return ($assetId && $documentId)
                    ? vsprintf(
                        '%s: %s/client/asset/%d/documents/%d',
                        [
                            $this->translator->trans('document_page', [], Template::TRANSLATE_DOMAIN),
                            $this->appFrontUrl,
                            $assetId,
                            $documentId
                        ]
                    )
                    : null;
            default:
                return null;
        }
    }
}
