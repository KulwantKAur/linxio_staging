<?php

namespace App\Events\User;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserUpdatedEvent extends Event
{
    public const NAME = 'app.event.user.updated';
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}