<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\ServiceRecordEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class ServiceRecordAddedPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::SERVICE_RECORD_ADDED => [
//                Event::TYPE_USER => function (ServiceRecord $entity) use ($event) {
//                    return [
//                        'reg_no' => $entity->getReminder()->getVehicle()
//                            ? $entity->getReminder()->getVehicle()->getRegNo() : self::DEFAULT_UNKNOWN,
//                        'entity' => $entity->getEntityString(),
//                        'model' => $entity->getReminder()->getVehicle()
//                            ? $entity->getReminder()->getVehicle()->getModel() : self::DEFAULT_UNKNOWN,
//                        'team' => $entity->getTeam()->isClientTeam()
//                            ? $entity->getTeam()->getClientName()
//                            : $entity->getTeam()->getType(),
//                        'status' => $entity->getStatus(),
//                        'title' => $entity->getReminder()->getTitle(),
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
            'reg_no' => 'regNoByReminder',
            'model' => 'modelByReminder',
            'team' => 'team',
            'status' => 'status',
            'title' =>'titleByReminder',
            'event_time' => 'updateTime',
            'triggered_by' => 'createdBy',
            'entity' => 'entity',
            'data_url' => 'dataUrl',
        ];
    }
}
