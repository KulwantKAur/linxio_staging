<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\ReminderEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class ServiceReminderExpiredPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::SERVICE_REMINDER_EXPIRED => [
//                Event::TYPE_USER => function (Reminder $entity) use ($event) {
//                    return [
//                        'reg_no' => $entity->getVehicle() ? $entity->getVehicle()->getRegNo() : null,
//                        'entity' =>  $entity->isVehicleReminder()
//                            ? $entity->getVehicle()->getRegNo()
//                            : (($entity->isAssetReminder() && $entity->getAsset())
//                                ? $entity->getAsset()->getName() : null),
//                        'model' => $entity->getVehicle() ? $entity->getVehicle()->getModel() : null,
//                        'team' => $entity->getTeam()->isClientTeam()
//                            ? $entity->getTeam()->getClientName()
//                            : $entity->getTeam()->getType(),
//                        'status' => $entity->getStatus(),
//                        'title' => $entity->getTitle(),
//                        'event_time' => $entity->getUpdatedAt()
//                            ? DateHelper::formatDate(
//                                $entity->getUpdatedAt(),
//                                DateHelper::FORMAT_DATE_SHORT_TIME,
//                                $entity->getTimeZoneName()
//                            ) : null,
//                        'data_url' => $this->getFrontendLinks($event, $entity)['data_url']
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
            'event_time' => 'updateTime',
            'entity' => 'entity',
            'data_url' => 'dataUrl',
        ];
    }
}
