<?php

namespace App\Events\User\Login;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserLoginEvent extends Event
{
    public const EVENT_USER_LOGIN = 'app.event.user.login';
    public const EVENT_NEW_USER_LOGIN = 'app.event.user.login.new';
    public const EVENT_BLOCKED_USER_LOGIN = 'app.event.user.login.blocked';
    public const EVENT_USER_REFRESH_TOKEN = 'app.event.user.token.refresh';

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