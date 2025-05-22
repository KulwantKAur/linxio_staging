<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\ClientEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class ClientDemoExpiredPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::CLIENT_DEMO_EXPIRED => [
//                Event::TYPE_USER => function (Client $entity) {
//                    return [
//                        'client_name' => $entity->getName(),
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
//                        'data_url' => sprintf('/admin/clients/%d', $entity->getId()),
//                    ];
//                },
//            ],
//        ];
//    }

    public function getInternalMappedPlaceholder(): array
    {
        return [
            'from_company' => 'fromCompany',
            'client_name' => 'clientName',
            'team' => 'team',
            'triggered_by' => 'updateBy',
            'event_time' => 'updateTime',
            'data_url' => 'dataUrl',
        ];
    }
}
