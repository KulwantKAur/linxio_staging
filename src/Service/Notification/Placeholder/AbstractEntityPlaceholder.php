<?php

namespace App\Service\Notification\Placeholder;

use App\Entity\Notification\Event;
use App\Entity\User;
use App\Service\Notification\Placeholder\Interfaces\EventEntityHandlerInterface;
use App\Service\Notification\Placeholder\Interfaces\PlaceholderInterface;
use Doctrine\Common\Util\ClassUtils;

abstract class AbstractEntityPlaceholder implements PlaceholderInterface
{
    protected $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    /**
     * @return array
     */
    public function getPlaceholder(EventEntityHandlerInterface $entity, ?User $user = null): array
    {
        $placeholder = [];

        if ($this->event->getEntity() === ClassUtils::getClass($entity->getEntity())) {
            $handlerPlaceholder = $entity->getValueHandlerPlaceholder($user);

            foreach ($this->getInternalMappedPlaceholder() as $keyPlaceholder => $entityKey) {
                $entityValue = array_key_exists($entityKey, $handlerPlaceholder);

                if ($entityValue === false) {
                    continue;
                }

                $placeholder[$keyPlaceholder] = $handlerPlaceholder[$entityKey];
            }
        }

        return $placeholder;
    }
}
