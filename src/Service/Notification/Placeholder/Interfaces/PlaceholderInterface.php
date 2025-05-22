<?php

namespace App\Service\Notification\Placeholder\Interfaces;

interface PlaceholderInterface
{
    /**
     * @return array
     */
    public function getPlaceholder(EventEntityHandlerInterface $entity): array;

    /**
     * @return array
     */
    public function getInternalMappedPlaceholder(): array;
}
