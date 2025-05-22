<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\UserEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class LoginAsClientPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::LOGIN_AS_CLIENT => [
//                Event::TYPE_USER => function (User $entity) {
//                    return [
//                        'client_email' => $entity->getName(),
//                        'client_name' => $entity->getClient()->getLegalName(),
//                        'event_time' => DateHelper::formatDate(
//                            new \DateTime(),
//                            DateHelper::FORMAT_DATE_SHORT_TIME,
//                            $entity->getTimezone()
//                        ),
//                    ];
//                },
//            ],
//        ];
//    }

    public function getInternalMappedPlaceholder(): array
    {
        return [
            'from_company' => 'fromCompany',
            'client_email' => 'clientEmail',
            'client_name' => 'clientName',
            'event_time' => 'lastLoggedTime',
        ];
    }
}
