<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\UserEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class LoginAsUserPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::LOGIN_AS_USER => [
//                Event::TYPE_USER => function (User $entity) {
//                    return [
//                        'user_email' => $entity->getEmail(),
//                        'user_name' => $entity->getFullName(),
//                        'event_time' => $entity->getLastLoggedAt()
//                            ? DateHelper::formatDate(
//                                $entity->getLastLoggedAt(),
//                                DateHelper::FORMAT_DATE_SHORT_TIME,
//                                $entity->getTimezone()
//                            ) : null,
//                    ];
//                },
//            ],
//        ];
//    }

    public function getInternalMappedPlaceholder(): array
    {
        return [
            'from_company' => 'fromCompany',
            'user_email' => 'userEmail',
            'user_name' => 'userName',
            'event_time' => 'lastLoggedTime',
        ];
    }
}
