<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\AssetEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class AssetDeletedPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::ASSET_DELETED => [
//                Event::TYPE_USER => function (Asset $entity) use ($event) {
//                    return [
//                        'asset_name' => $entity->getName(),
//                        'team' => $entity->getTeam()->isClientTeam()
//                            ? $entity->getTeam()->getClientName()
//                            : $entity->getTeam()->getType(),
//                        'event_time' => $entity->getUpdatedAt()
//                            ? DateHelper::formatDate(
//                                $entity->getUpdatedAt(),
//                                DateHelper::FORMAT_DATE_SHORT_TIME,
//                                $entity->getTimeZoneName()
//                            ) : null,
//                        'triggered_by' => $entity->getUpdatedBy() ? $entity->getUpdatedBy()->getFullName() : ''
//                    ];
//                }
//            ],
//        ];
//    }

    public function getInternalMappedPlaceholder(): array
    {
        return [
            'from_company' => 'fromCompany',
            'asset_name' => 'assetName',
            'team' => 'team',
            'event_time' => 'updateTime',
            'triggered_by' => 'updateBy',
        ];
    }
}
