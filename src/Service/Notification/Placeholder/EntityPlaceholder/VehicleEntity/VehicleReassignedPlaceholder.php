<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\VehicleEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class VehicleReassignedPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::VEHICLE_REASSIGNED => [
//                Event::TYPE_USER => function (Vehicle $entity) use ($context, $event) {
//                    return [
//                        'reg_no' => $entity->getRegNo(),
//                        'model' => $entity->getModel(),
//                        'team' => $entity->getTeam()->isClientTeam()
//                            ? $entity->getTeam()->getClientName()
//                            : $entity->getTeam()->getType(),
//                        'driver' => $entity->getDriverName() ? $entity->getDriverName() : null,
//                        'status' => $entity->getStatus(),
//                        'triggered_by' => $entity->getUpdatedByName() ? $entity->getUpdatedByName() : null,
//                        'event_time' => $entity->getUpdatedAt()
//                            ? DateHelper::formatDate(
//                                $entity->getUpdatedAt(),
//                                DateHelper::FORMAT_DATE_SHORT_TIME,
//                                $entity->getTimeZoneName()
//                            ) : null,
//                        'old_value' => !is_null($context['oldValue'])
//                            ? vsprintf('from driver "%s"', [$context['oldValue']])
//                            : null,
//                        'reg_no_with_model' => $entity->getId()
//                            ? !empty($entity->getModel())
//                                ? vsprintf(
//                                    '%s (%s)',
//                                    [$entity->getRegNo(), $entity->getModel()]
//                                )
//                                : vsprintf('%s', [$entity->getRegNo()])
//                            : self::DEFAULT_UNKNOWN,
//                        'vehicle_url' => $this->getFrontendLinks($event, $entity)['vehicle_url'],
//                        'driver_url' => $this->getFrontendLinks($event, $entity)['driver_url'],
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
            'driver' => 'driver',
            'status' => 'status',
            'triggered_by' => 'updateBy',
            'event_time' => 'updateTime',
            'old_value' => 'oldValueDriver',
            'reg_no_with_model' => 'regNoWithModel',
            'vehicle_url' => 'vehicleUrl',
            'driver_url' => 'driverUrl',
        ];
    }
}
