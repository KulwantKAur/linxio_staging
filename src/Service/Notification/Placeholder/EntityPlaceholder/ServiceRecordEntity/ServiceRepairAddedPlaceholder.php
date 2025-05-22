<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\ServiceRecordEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class ServiceRepairAddedPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::SERVICE_REPAIR_ADDED => [
//                Event::TYPE_USER => function (ServiceRecord $entity) use ($event) {
//                    return [
//                        'reg_no' => $entity->getRepairVehicle()
//                            ? $entity->getRepairVehicle()->getRegNo() : self::DEFAULT_UNKNOWN,
//                        'entity' => $entity->getEntityString(),
//                        'model' => $entity->getRepairVehicle()
//                            ? $entity->getRepairVehicle()->getModel() : self::DEFAULT_UNKNOWN,
//                        'asset' => $entity->getRepairAsset() ? $entity->getRepairAsset()->getName() : null,
//                        'status' => $entity->getStatus(),
//                        'title' => $entity->getRepairData()->getTitle(),
//                        'event_time' => $entity->getUpdatedAt()
//                            ? DateHelper::formatDate(
//                                $entity->getUpdatedAt(),
//                                DateHelper::FORMAT_DATE_SHORT_TIME,
//                                $entity->getTimeZoneName()
//                            ) : self::DEFAULT_UNKNOWN,
//                        'data_url' => $this->getFrontendLinks($event, $entity)['data_url'],
//                        'triggered_by' => $entity->getCreatedBy()
//                            ? $entity->getCreatedBy()->getFullName() : self::DEFAULT_UNKNOWN,
//                    ];
//                },
//            ],
//        ];
//    }

    public function getInternalMappedPlaceholder(): array
    {
        return [
            'from_company' => 'fromCompany',
            'reg_no' => 'regNoByRepair',
            'model' => 'modelByRepair',
            'status' => 'status',
            'title' => 'titleByRepair',
            'event_time' => 'updateTime',
            'triggered_by' => 'createdBy',
            'entity' => 'entity',
            'asset' => 'asset',
            'data_url' => 'dataUrl',
        ];
    }
}
