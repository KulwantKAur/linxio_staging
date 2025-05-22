<?php

namespace App\Events\UserGroup;

use App\Entity\User;
use App\Entity\UserGroup;
use Symfony\Contracts\EventDispatcher\Event;

class UserGroupChangedScopeEvent extends Event
{
    const NAME = 'app.event.userGroup.changedScope';
    protected $userGroup;
    protected $user;
    protected $vehiclesIdsToAdd;
    protected $vehiclesIdsToRemove;

    /**
     * UserGroupChangedScopeEvent constructor.
     * @param UserGroup $userGroup
     * @param User $currentUser
     * @param array $vehiclesIdsToAdd
     * @param array $vehiclesIdsToRemove
     */
    public function __construct(
        UserGroup $userGroup,
        User $currentUser,
        array $vehiclesIdsToAdd = [],
        array $vehiclesIdsToRemove = []
    ) {
        $this->userGroup = $userGroup;
        $this->user = $currentUser;
        $this->vehiclesIdsToAdd = $vehiclesIdsToAdd;
        $this->vehiclesIdsToRemove = $vehiclesIdsToRemove;
    }

    /**
     * @return UserGroup
     */
    public function getUserGroup(): UserGroup
    {
        return $this->userGroup;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return array
     */
    public function getVehiclesIdsToAdd(): array
    {
        return $this->vehiclesIdsToAdd;
    }

    /**
     * @return array
     */
    public function getVehiclesIdsToRemove(): array
    {
        return $this->vehiclesIdsToRemove;
    }
}