<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\RouteEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class DigitalFormIsNotCompletedPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::DIGITAL_FORM_IS_NOT_COMPLETED => [
//                Event::TYPE_SYSTEM => function (Route $entity) use ($context, $event) {
//                    return [
//                        'reg_no' => $entity->getVehicle() ? $entity->getVehicle()->getRegNo() : null,
//                        'driver' => $entity->getVehicle()
//                            ? ($entity->getVehicle()->getDriverName() ?? null)
//                            : null,
//                        'event_time' => $entity->getStartedAt()
//                            ? DateHelper::formatDate(
//                                $entity->getStartedAt(),
//                                DateHelper::FORMAT_DATE_SHORT_TIME,
//                                $entity->getTimeZoneName()
//                            ) : null,
//                        'form_title' => !empty($context['form'])
//                            ? implode(",", $context['form']) : self::DEFAULT_UNKNOWN,
//                        'vehicle_url' => $this->getFrontendLinks($event, $entity)['vehicle_url'],
//                        'driver_url' => $this->getFrontendLinks($event, $entity)['driver_url'],
//                    ];
//                },
//                Event::TYPE_USER => function (Route $entity) use ($context, $event) {
//                    return [
//                        'reg_no' => $entity->getVehicle() ? $entity->getVehicle()->getRegNo() : null,
//                        'driver' => $entity->getVehicle()
//                            ? ($entity->getVehicle()->getDriverName() ?? null)
//                            : null,
//                        'event_time' => $entity->getStartedAt()
//                            ? DateHelper::formatDate(
//                                $entity->getStartedAt(),
//                                DateHelper::FORMAT_DATE_SHORT_TIME,
//                                $entity->getTimeZoneName()
//                            ) : null,
//                        'form_title' => !empty($context['form'])
//                            ? implode(",", $context['form']) : self::DEFAULT_UNKNOWN,
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
            'driver' => 'driver',
            'event_time' => 'startedAtTime',
            'form_title' => 'formTitle',
            'vehicle_url' => 'vehicleUrl',
            'driver_url' => 'driverUrl',
        ];
    }
}
