<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\VehicleEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class VehicleChangedModelPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::VEHICLE_CHANGED_MODEL => [
//                Event::TYPE_USER => function (Vehicle $entity) use ($context, $event) {
//                    return [
//                        'reg_no' => $entity->getRegNo(),
//                        'model' => $entity->getModel(),
//                        'team' => $entity->getTeam()->isClientTeam()
//                            ? $entity->getTeam()->getClientName()
//                            : $entity->getTeam()->getType(),
//                        'triggered_by' => $entity->getUpdatedByName(),
//                        'event_time' => $entity->getUpdatedAt()
//                            ? DateHelper::formatDate(
//                                $entity->getUpdatedAt(),
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
//                        'old_value' => $context['oldValue'] ?? self::DEFAULT_UNKNOWN,
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
            'triggered_by' => 'updateBy',
            'event_time' => 'updateTime',
            'reg_no_with_model' => 'regNoWithModel',
            'old_value' => 'oldValue',
            'vehicle_url' => 'vehicleUrl',
        ];
    }
}
