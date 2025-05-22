<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\ClientEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class ClientCreatedPlaceholder extends AbstractEntityPlaceholder
{
//    public function getPlaceholder(): array
//    {
//        return [
//            Event::CLIENT_CREATED => [
//                Event::TYPE_USER => function (Client $entity) {
//                    return [
//                        'client_name' => $entity->getName(),
//                        'team' => $entity->getTeam()->isClientTeam()
//                            ? $entity->getTeam()->getClientName()
//                            : $entity->getTeam()->getType(),
//                        'triggered_by' => $entity->getCreatedByName(),
//                        'event_time' => $entity->getCreatedAt()
//                            ? DateHelper::formatDate(
//                                $entity->getCreatedAt(),
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
            'triggered_by' => 'createdBy',
            'event_time' => 'createdTime',
            'data_url' => 'dataUrl',
        ];
    }
}
