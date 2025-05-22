<?php


namespace App\Events\User;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserDeletedEvent extends Event
{
    const NAME = 'app.event.user.deleted';

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