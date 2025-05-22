<?php

namespace App\Events\Sensor;

use App\Entity\User;
use App\Events\User\UserPreUpdatedEvent;
use Symfony\Contracts\EventDispatcher\Event;

class SensorUserUpdatedEvent extends Event
{
    const NAME = 'app.event.sensor.userUpdated';
    protected $user;
    protected $currentUser;
    protected $userPreUpdatedEvent;

    /**
     * SensorUserUpdatedEvent constructor.
     * @param User $user
     * @param User $currentUser
     * @param UserPreUpdatedEvent|null $userPreUpdatedEvent
     */
    public function __construct(User $user, User $currentUser, UserPreUpdatedEvent $userPreUpdatedEvent = null)
    {
        $this->user = $user;
        $this->currentUser = $currentUser;
        $this->userPreUpdatedEvent = $userPreUpdatedEvent;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return UserPreUpdatedEvent|null
     */
    public function getUserPreUpdatedEvent(): ?UserPreUpdatedEvent
    {
        return $this->userPreUpdatedEvent;
    }

    /**
     * @param UserPreUpdatedEvent|null $userPreUpdatedEvent
     */
    public function setUserPreUpdatedEvent(?UserPreUpdatedEvent $userPreUpdatedEvent): void
    {
        $this->userPreUpdatedEvent = $userPreUpdatedEvent;
    }

    /**
     * @return User
     */
    public function getCurrentUser(): User
    {
        return $this->currentUser;
    }
}