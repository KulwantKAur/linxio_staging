<?php

declare(strict_types=1);

namespace App\Service\EventLog\Mapper;

use App\Entity\Invoice;
use App\Entity\Notification\Event;
use App\Entity\User;

/**
 *
 */
class InvoiceEventLog extends EventLog
{
    protected array $context;

    /**
     * InvoiceEventLog constructor.
     * @param Invoice $entity
     * @param User|null $currentUser
     * @param Event $event
     * @param array $context
     */
    public function __construct(Invoice $entity, ?User $currentUser, Event $event, array $context = [])
    {
        $this->entity = $entity;
        $this->currentUser = $currentUser;
        $this->currentEvent = $event;
        $this->context = $context;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getDetails()
    {
        $context['context'] = $this->context ?? null;
        $entityDetails = $this->entity->toArray(Invoice::DEFAULT_DISPLAY_VALUES);

        return array_merge($entityDetails, $context);
    }

    /**
     * @return string
     */
    public function getTeamData()
    {
        return $this->entity->getClient()?->getName();
    }

    /**
     * @return mixed
     */
    public function getEventSource()
    {
        return $this->entity?->getInternalInvoiceId();
    }

    /**
     * @return null
     */
    public function getEntityId()
    {
        return $this->entity->getId();
    }

    /**
     * @return int|null
     */
    public function getEntityTeamId(): ?int
    {
        return $this->entity->getClient()?->getTeamId();
    }

    /**
     * Team, who changed this object
     * @return int|null
     */
    public function getTeamBy(): ?int
    {
        return $this->currentUser?->getTeam()->getId();
    }

    /**
     * User id, who changed this object
     * @return int|null
     */
    public function getUserBy(): ?int
    {
        return (int)$this->currentUser?->getId();
    }
}
