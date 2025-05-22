<?php

namespace App\Service\Notification\Placeholder\EntityPlaceholder\UserEntity;

use App\Service\Notification\Placeholder\AbstractEntityPlaceholder;

class AccessLevelChangedPlaceholder extends AbstractEntityPlaceholder
{
    public function getInternalMappedPlaceholder(): array
    {
        return [
            'user_email' => 'userEmail',
            'user_name' => 'userName',
            'event_time' => 'eventTime',
            'triggered_by' => 'triggeredByContext',
            'old_role' => 'oldRole',
            'new_role' => 'newRole'
        ];
    }
}
