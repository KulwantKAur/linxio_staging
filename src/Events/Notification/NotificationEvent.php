<?php

namespace App\Events\Notification;

use Symfony\Contracts\EventDispatcher\Event;
use \App\Entity\BaseEntity;
use Symfony\Component\Security\Core\User\UserInterface;

class NotificationEvent extends Event
{
    public const NAME = 'app.event.notification.base_event';

    private $eventName;
    private $entity;
    private $dt;
    private $currentUser;
    private $context;

    /**
     * NotificationEvent constructor.
     * @param string $eventName
     * @param $entity
     * @param \DateTime|null $dt
     * @param UserInterface|null $currentUser
     * @param array $context
     */
    public function __construct(
        string $eventName,
        $entity,
        ?\DateTime $dt = null,
        ?UserInterface $currentUser = null,
        array $context = []
    ) {
        $this->eventName = $eventName;
        $this->entity = $entity;
        $this->dt = $dt;
        $this->currentUser = $currentUser;
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getEventName(): string
    {
        return $this->eventName;
    }

    /**
     * @return BaseEntity|object
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return \DateTime|null
     */
    public function getDt(): ?\DateTime
    {
        return $this->dt;
    }

    /**
     * @return UserInterface|null
     */
    public function getCurrentUser(): ?UserInterface
    {
        return $this->currentUser;
    }

    public function getContext()
    {
        return $this->context;
    }
}
