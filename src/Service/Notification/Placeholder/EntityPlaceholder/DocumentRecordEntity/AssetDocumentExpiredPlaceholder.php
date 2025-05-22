<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\DocumentRecordEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class AssetDocumentExpiredPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::ASSET_DOCUMENT_EXPIRED => [
//                Event::TYPE_USER => function (DocumentRecord $entity) use ($event) {
//                    return [
//                        'asset' => $entity->getAsset()
//                            ? $entity->getAsset()->getName()
//                            : self::DEFAULT_UNKNOWN,
//                        'team' => $entity->getDocument()
//                            ? $entity->getAsset()->getTeam()->isClientTeam()
//                                ? $entity->getAsset()->getTeam()->getClientName()
//                                : $entity->getAsset()->getTeam()->getType()
//                            : self::DEFAULT_UNKNOWN,
//                        'title' => $entity->getDocument()->getTitle(),
//                        'status' => $entity->getDocument()->getStatus(),
//                        'triggered_by' => $entity->getUpdatedBy()
//                            ? $entity->getUpdatedBy()->getName() : self::DEFAULT_UNKNOWN,
//                        'event_time' => $entity->getUpdatedAt()
//                            ? DateHelper::formatDate(
//                                $entity->getUpdatedAt(),
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
            'title' => 'title',
            'status' => 'status',
            'triggered_by' => 'updateBy',
            'event_time' => 'updateTime',
            'exp_date' => 'expirationDate',
            'data_url' => 'dataUrl',
            'data_by_type' => 'dataByTypeDocument',
        ];
    }
}
