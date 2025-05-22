<?php


namespace App\Events\User;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserArchivedEvent extends Event
{
    const NAME = 'app.event.user.archived';

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