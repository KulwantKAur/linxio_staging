<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\DigitalFormAnswerEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class DigitalFormWithFailPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::DIGITAL_FORM_WITH_FAIL => [
//                Event::TYPE_USER => function (DigitalFormAnswer $entity) use ($context, $event) {
//                    return [
//                        'reg_no' => $entity->getVehicle()
//                            ? $entity->getVehicle()->getRegNo() : self::DEFAULT_UNKNOWN,
//                        'event_time' => DateHelper::formatDate(
//                            $entity->getCreatedAt(),
//                            DateHelper::FORMAT_DATE_SHORT_TIME,
//                            $entity->getVehicle() ? $entity->getVehicle()->getTimeZoneName() : null
//                        ),
//                        'form_title' => $entity->getDigitalForm() ? $entity->getDigitalForm()->getTitle() : null,
//                        'driver' => $entity->getUser() ? $entity->getUser()->getFullName() : null,
//                        'data_url' => $this->getFrontendLinks($event, $entity)['data_url'],
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
            'event_time' => 'createdTime',
            'form_title' => 'formTitle',
            'driver' => 'driver',
            'data_url' => 'dataUrl',
        ];
    }
}
