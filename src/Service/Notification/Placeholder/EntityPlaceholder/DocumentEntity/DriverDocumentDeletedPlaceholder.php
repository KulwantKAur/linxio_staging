<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\DocumentEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class DriverDocumentDeletedPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::DRIVER_DOCUMENT_DELETED => [
//                Event::TYPE_USER => function (Document $entity) use ($event) {
//                    return [
//                        'reg_no' => $entity->getRegNo(),
//                        'model' => $entity->getVehicle() ? $entity->getVehicle()->getModel() : null,
//                        'team' => $entity->getTeam()->isClientTeam()
//                            ? $entity->getTeam()->getClientName()
//                            : $entity->getTeam()->getType(),
//                        'status' => $entity->getStatus(),
//                        'title' => $entity->getTitle(),
//                        'triggered_by' => $entity->getUpdatedBy()->getName(),
//                        'event_time' => $entity->getUpdatedAt()
//                            ? DateHelper::formatDate(
//                                $entity->getUpdatedAt(),
//                                DateHelper::FORMAT_DATE_SHORT_TIME,
//                                $entity->getTimeZoneName()
//                            ) : null,
//                        'driver' => $entity->getVehicle()
//                            ? ($entity->getVehicle()->getDriverName() ?? self::DEFAULT_UNKNOWN)
//                            : self::DEFAULT_UNKNOWN,
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
            'reg_no' => 'regNo',
            'model' => 'model',
            'team' => 'team',
            'status' => 'status',
            'title' => 'title',
            'triggered_by' => 'updateBy',
            'event_time' => 'updateTime',
            'driver' => 'driver',
            'data_url' => 'dataUrl',
            'data_by_type' => 'dataByTypeDocument',
        ];
    }
}
