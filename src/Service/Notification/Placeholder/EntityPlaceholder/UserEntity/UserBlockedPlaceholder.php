<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\UserEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class UserBlockedPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::USER_BLOCKED => [
//                Event::TYPE_USER => function (User $entity) use ($event) {
//                    return [
//                        'user_email' => $entity->getEmail(),
//                        'user_name' => $entity->getFullName(),
//                        'team' => $entity->isInClientTeam()
//                            ? $entity->getTeam()->getClientName()
//                            : $entity->getTeam()->getType(),
//                        'data_message' => $entity->getBlockingMessage(),
//                        'triggered_by' => $entity->getUpdatedByName(),
//                        'event_time' => $entity->getUpdatedAt()
//                            ? DateHelper::formatDate(
//                                $entity->getUpdatedAt(),
//                                DateHelper::FORMAT_DATE_SHORT_TIME,
//                                $entity->getTimezone()
//                            ) : null,
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
            'user_email' => 'userEmail',
            'user_name' => 'userName',
            'team' => 'team',
            'data_message' => 'dataMessage',
            'triggered_by' => 'triggeredBy',
            'event_time' => 'updateTime',
            'data_url' => 'dataUrl',
        ];
    }
}
