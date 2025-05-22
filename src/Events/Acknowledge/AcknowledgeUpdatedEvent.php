<?php

namespace App\Events\Acknowledge;

use App\Entity\Acknowledge;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class AcknowledgeUpdatedEvent extends Event
{
    public const NAME = 'app.event.acknowledge.updated';
    protected $acknowledge;
    protected $user;

    public function __construct(Acknowledge $acknowledge, User $user)
    {
        $this->acknowledge = $acknowledge;
        $this->user = $user;
    }

    public function getAcknowledge(): Acknowledge
    {
        return $this->acknowledge;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}