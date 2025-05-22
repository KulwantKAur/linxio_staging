<?php

namespace App\Events\UserGroup;

use App\Entity\User;
use App\Entity\UserGroup;
use Symfony\Contracts\EventDispatcher\Event;

class UserRemovedFromUserGroupEvent extends Event
{
    const NAME = 'app.event.userGroup.userRemoved';
    protected $user;
    protected $group;

    /**
     * UserRemovedFromGroupEvent constructor.
     * @param User $user
     * @param UserGroup $group
     */
    public function __construct(User $user, UserGroup $group)
    {
        $this->user = $user;
        $this->group = $group;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return UserGroup
     */
    public function getGroup(): UserGroup
    {
        return $this->group;
    }
}