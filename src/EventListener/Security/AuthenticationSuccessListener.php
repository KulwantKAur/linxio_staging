<?php

namespace App\EventListener\Security;

use App\Entity\User;
use App\Events\User\Login\UserLoginEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

class AuthenticationSuccessListener
{
    private $eventDispatcher;

    /**
     * AuthenticationSuccessListener constructor.
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function onAuthenticationSuccess(AuthenticationEvent $event)
    {
        /** @var User $user */
        $user = $event->getAuthenticationToken()->getUser();

        if ($user instanceof User) {
            $this->processUserEvents($user);
        }
    }

    /**
     * @param User $user
     */
    private function processUserEvents(User $user)
    {
        switch ($user->getStatus()) {
            case User::STATUS_BLOCKED:
                $this->eventDispatcher->dispatch(new UserLoginEvent($user), UserLoginEvent::EVENT_BLOCKED_USER_LOGIN);
                break;
            case User::STATUS_NEW:
                $this->eventDispatcher->dispatch(new UserLoginEvent($user), UserLoginEvent::EVENT_NEW_USER_LOGIN);
                break;
        }
    }
}