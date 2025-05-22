<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\UserEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class AdminUserDeletedPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::ADMIN_USER_DELETED => [
//                Event::TYPE_USER => function (User $entity) use ($event) {
//                    return [
//                        'user_email' => $entity->getEmail(),
//                        'user_name' => $entity->getFullName(),
//                        'team' => $entity->isInClientTeam()
//                            ? $entity->getTeam()->getClientName()
//                            : $entity->getTeam()->getType(),
//                        'triggered_by' => $entity->getUpdatedByName(),
//                        'event_time' => $entity->getUpdatedAt()
//                            ? DateHelper::formatDate(
//                                $entity->getUpdatedAt(),
//                                DateHelper::FORMAT_DATE_SHORT_TIME,
//                                $entity->getTimezone()
//                            ) : null,
//                        'data_url' => $this->getFrontendLinks($event, $entity)['data_url'],
//                    ];
//                }
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
            'triggered_by' => 'triggeredBy',
            'event_time' => 'updateTime',
            'data_url' => 'dataUrl',
        ];
    }
}
