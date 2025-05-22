<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\DocumentRecordEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class DriverDocumentExpireSoonPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::DRIVER_DOCUMENT_EXPIRE_SOON => [
//                Event::TYPE_USER => function (DocumentRecord $entity) use ($event) {
//                    return [
//                        'reg_no' => $entity->getVehicle() ? $entity->getVehicle()->getRegNo() : self::DEFAULT_UNKNOWN,
//                        'model' => $entity->getVehicle() ? $entity->getVehicle()->getModel() : self::DEFAULT_UNKNOWN,
//                        'team' => $entity->getDocument()
//                            ? $entity->getVehicle()
//                                ? $entity->getVehicle()->getTeam()->isClientTeam()
//                                    ? $entity->getVehicle()->getTeam()->getClientName()
//                                    : $entity->getVehicle()->getTeam()->getType()
//                                : self::DEFAULT_UNKNOWN
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
//                        'driver' => $entity->getDocument()->getDriver()
//                            ? $entity->getDocument()->getDriver()->getFullName()
//                            : self::DEFAULT_UNKNOWN,
//                        'data_url' => $this->getFrontendLinks($event, $entity)['data_url'],
//                        'data_by_type' => $this->getFrontendLinks($event, $entity)['data_by_type'],
//                    ];
//                },
//            ],
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
            'expiration_date' => 'expirationDate',
            'driver' => 'driver',
            'data_url' => 'dataUrl',
            'data_by_type' => 'dataByTypeDocument',
        ];
    }
}
