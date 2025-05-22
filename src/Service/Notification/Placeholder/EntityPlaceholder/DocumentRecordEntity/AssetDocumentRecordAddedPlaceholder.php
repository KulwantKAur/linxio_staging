<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\DocumentRecordEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class AssetDocumentRecordAddedPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::TYPE_USER => function (DocumentRecord $entity) use ($event) {
//                return [
//                    'asset' => $entity->getAsset()
//                        ? $entity->getAsset()->getName() : self::DEFAULT_UNKNOWN,
//                    'title' => $entity->getDocument()->getTitle(),
//                    'status' => $entity->getDocument()->getStatus(),
//                    'triggered_by' => $entity->getCreatedBy()
//                        ? $entity->getCreatedBy()->getFullName() : self::DEFAULT_UNKNOWN,
//                    'event_time' => $entity->getCreatedAt()
//                        ? DateHelper::formatDate(
//                            $entity->getCreatedAt(),
//                            DateHelper::FORMAT_DATE_SHORT_TIME,
//                            $entity->getTimeZoneName()
//                        ) : self::DEFAULT_UNKNOWN,
//                    'data_url' => $this->getFrontendLinks($event, $entity)['data_url'],
//                    'data_by_type' => $this->getFrontendLinks($event, $entity)['data_by_type'],
//                ];
//            },
//        ];
//    }

    public function getInternalMappedPlaceholder(): array
    {
        return [
            'from_company' => 'fromCompany',
            'asset' => 'asset',
            'title' => 'title',
            'status' => 'status',
            'triggered_by' => 'createdBy',
            'event_time' => 'createdTime',
            'data_url' => 'dataUrl',
            'data_by_type' => 'dataByTypeDocument',
        ];
    }
}
