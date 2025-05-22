<?php

namespace App\Events\User;

use App\Entity\User;
use App\Entity\UserGroup;
use Symfony\Contracts\EventDispatcher\Event;

class UserRemovedFromGroupEvent extends Event
{
    const NAME = 'app.event.user.removeFromGroup';
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