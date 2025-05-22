<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\TrackerHistoryEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class VehicleDrivingWithoutDriverPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::VEHICLE_DRIVING_WITHOUT_DRIVER => [
//                Event::TYPE_USER => function (Route $entity) use ($event) {
//                    return [
//                        'reg_no' => $entity->getVehicle()->getRegNo(),
//                        'team' => $entity->getVehicle()
//                            ? $entity->getVehicle()->getTeam()->isClientTeam()
//                                ? $entity->getVehicle()->getTeam()->getClientName()
//                                : $entity->getVehicle()->getTeam()->getType()
//                            : self::DEFAULT_UNKNOWN,
//                        'model' => $entity->getVehicle()
//                            ? $entity->getVehicle()->getModel() : self::DEFAULT_UNKNOWN,
//                        'event_time' => $entity->getStartedAt()
//                            ? DateHelper::formatDate(
//                                $entity->getStartedAt(),
//                                DateHelper::FORMAT_DATE_SHORT_TIME,
//                                $entity->getTimeZoneName()
//                            ) : null,
//                        'reg_no_with_model' => $entity->getVehicle()
//                            ? !empty($entity->getVehicle()->getModel())
//                                ? vsprintf(
//                                    '%s (%s)',
//                                    [$entity->getVehicle()->getRegNo(), $entity->getVehicle()->getModel()]
//                                )
//                                : vsprintf('%s', [$entity->getVehicle()->getRegNo()])
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
            'team' => 'team',
            'model' => 'model',
            'event_time' => 'tsTime',
            'reg_no_with_model' => 'regNoWithModel',
            'vehicle_url' => 'vehicleUrl',
        ];
    }
}
