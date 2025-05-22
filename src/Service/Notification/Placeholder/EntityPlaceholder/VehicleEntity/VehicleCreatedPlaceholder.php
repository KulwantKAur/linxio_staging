<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\VehicleEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class VehicleCreatedPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::VEHICLE_CREATED => [
//                Event::TYPE_USER => function (Vehicle $entity) use ($event) {
//                    return [
//                        'reg_no' => $entity->getRegNo(),
//                        'model' => $entity->getModel(),
//                        'team' => $entity->getTeam()->isClientTeam()
//                            ? $entity->getTeam()->getClientName()
//                            : $entity->getTeam()->getType(),
//                        'triggered_by' => $entity->getCreatedByName(),
//                        'event_time' => $entity->getCreatedAt()
//                            ? DateHelper::formatDate(
//                                $entity->getCreatedAt(),
//                                DateHelper::FORMAT_DATE_SHORT_TIME,
//                                $entity->getTimeZoneName()
//                            ) : null,
//                        'reg_no_with_model' => $entity->getId()
//                            ? !empty($entity->getModel())
//                                ? vsprintf(
//                                    '%s (%s)',
//                                    [$entity->getRegNo(), $entity->getModel()]
//                                )
//                                : vsprintf('%s', [$entity->getRegNo()])
//                            : self::DEFAULT_UNKNOWN,
//                        'vehicle_url' => $this->getFrontendLinks($event, $entity)['vehicle_url'],
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
            'triggered_by' => 'createdBy',
            'event_time' => 'createdTime',
            'reg_no_with_model' => 'regNoWithModel',
            'vehicle_url' => 'vehicleUrl',
        ];
    }
}
