<?php

namespace App\Service\Notification;

use App\Entity\BaseEntity;
use App\Entity\User;
use App\Events\Notification\NotificationEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EventDispatcher
{
    private $eventDispatcher;
    private $tokenStorage;
    private $eventStorage = [];

    public function __construct(EventDispatcherInterface $eventDispatcher, TokenStorageInterface $tokenStorage)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param string $name
     * @param $entity
     * @param \DateTime|null $dt
     * @param array $context
     */
    public function dispatch(string $name, $entity, ?\DateTime $dt = null, array $context = [])
    {
        /** @var User|null $currentUser */
        $currentUser = $this->tokenStorage->getToken() && $this->tokenStorage->getToken()->getUser() instanceof User
            ? $this->tokenStorage->getToken()->getUser()
            : null;
        $this->eventDispatcher->dispatch(
            new NotificationEvent($name, $entity, $dt, $currentUser, $context),
            NotificationEvent::NAME
        );
    }

    public function addEvent(string $name, BaseEntity $entity, ?\DateTime $dt = null, array $context = [])
    {
        $this->eventStorage[] = [
            'name' => $name,
            'entity' => $entity,
            'dt' => $dt,
            'context' => $context
        ];
    }

    public function dispatchEventStorage()
    {
        foreach ($this->eventStorage as $eventData) {
            $this->dispatch($eventData['name'], $eventData['entity'], $eventData['dt'], $eventData['context']);
        }
        $this->eventStorage = [];
    }
}
