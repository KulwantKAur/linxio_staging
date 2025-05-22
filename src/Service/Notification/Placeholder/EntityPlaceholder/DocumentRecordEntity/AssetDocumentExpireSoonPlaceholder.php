<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\DocumentRecordEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class AssetDocumentExpireSoonPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::ASSET_DOCUMENT_EXPIRE_SOON => [
//                Event::TYPE_USER => function (DocumentRecord $entity) use ($event) {
//                    return [
//                        'asset' => $entity->getAsset() ? $entity->getAsset()->getName() : self::DEFAULT_UNKNOWN,
//                        'team' => $entity->getAsset()
//                            ? $entity->getAsset()->getTeam()->isClientTeam()
//                                ? $entity->getAsset()->getTeam()->getClientName()
//                                : $entity->getAsset()->getTeam()->getType()
//                            : self::DEFAULT_UNKNOWN,
//                        'status' => $entity->getDocument()->getStatus(),
//                        'title' => $entity->getDocument() ?
//                            $entity->getDocument()->getTitle() : self::DEFAULT_UNKNOWN,
//                        'triggered_by' => $entity->getUpdatedBy()
//                            ? $entity->getUpdatedBy()->getName() : self::DEFAULT_UNKNOWN,
//                        'event_time' => $entity->getUpdatedAt()
//                            ? DateHelper::formatDate(
//                                $entity->getUpdatedAt(),
//                                DateHelper::FORMAT_DATE_SHORT_TIME,
//                                $entity->getTimeZoneName()
//                            ) : self::DEFAULT_UNKNOWN,
//                        'expiration_date' => $entity->getExpDate()
//                            ? DateHelper::formatDate(
//                                $entity->getExpDate(),
//                                DateHelper::FORMAT_DATE_SHORT_TIME,
//                                $entity->getTimeZoneName()
//                            ) : self::DEFAULT_UNKNOWN,
//                        'data_url' => $this->getFrontendLinks($event, $entity)['data_url'],
//                        'data_by_type' => $this->getFrontendLinks($event, $entity)['data_by_type'],
//                    ];
//                },
//            ],
//        ];
//    }

    public function getInternalMappedPlaceholder(): array
    {
        return [
            'from_company' => 'fromCompany',
            'asset' => 'asset',
            'team' => 'team',
            'status' => 'status',
            'title' => 'title',
            'triggered_by' => 'updateBy',
            'event_time' => 'updateTime',
            'expiration_date' => 'expirationDate',
            'data_url' => 'dataUrl',
            'data_by_type' => 'dataByTypeDocument',
        ];
    }
}
