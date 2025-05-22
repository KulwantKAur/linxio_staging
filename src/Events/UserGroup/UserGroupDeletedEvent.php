<?php

namespace App\Events\UserGroup;

use App\Entity\User;
use App\Entity\UserGroup;
use Symfony\Contracts\EventDispatcher\Event;

class UserGroupDeletedEvent extends Event
{
    const NAME = 'app.event.userGroup.deleted';
    protected $userGroup;
    protected $user;

    public function __construct(UserGroup $userGroup, User $user)
    {
        $this->userGroup = $userGroup;
        $this->user = $user;
    }

    public function getUserGroup(): UserGroup
    {
        return $this->userGroup;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}