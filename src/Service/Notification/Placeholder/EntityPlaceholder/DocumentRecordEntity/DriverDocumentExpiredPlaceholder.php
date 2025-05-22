<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\DocumentRecordEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class DriverDocumentExpiredPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::DRIVER_DOCUMENT_EXPIRED => [
//                Event::TYPE_USER => function (DocumentRecord $entity) use ($event) {
//                    return [
//                        'reg_no' => $entity->getDocument()->getVehicle()
//                            ? $entity->getDocument()->getVehicle()->getRegNo() : self::DEFAULT_UNKNOWN,
//                        'model' => $entity->getDocument()->getVehicle()
//                            ? $entity->getDocument()->getVehicle()->getModel() : self::DEFAULT_UNKNOWN,
//                        'team' => $entity->getDocument()
//                            ? $entity->getDocument()->getVehicle()
//                                ? $entity->getDocument()->getVehicle()->getTeam()->isClientTeam()
//                                    ? $entity->getDocument()->getVehicle()->getTeam()->getClientName()
//                                    : $entity->getDocument()->getVehicle()->getTeam()->getType()
//                                : self::DEFAULT_UNKNOWN
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
//                        'driver' => $entity->getDocument()->getDriver()
//                            ? $entity->getDocument()->getDriver()->getFullName()
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
            'title' => 'title',
            'status' => 'status',
            'triggered_by' => 'updateBy',
            'event_time' => 'updateTime',
            'exp_date' => 'expirationDate',
            'driver' => 'driver',
            'data_url' => 'dataUrl',
            'data_by_type' => 'dataByTypeDocument',
        ];
    }
}
