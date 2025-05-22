<?php

namespace App\Events\Acknowledge;

use App\Entity\Acknowledge;
use Symfony\Contracts\EventDispatcher\Event;

class AcknowledgeCreatedEvent extends Event
{
    public const NAME = 'app.event.acknowledge.created';
    protected $acknowledge;

    public function __construct(Acknowledge $acknowledge)
    {
        $this->acknowledge = $acknowledge;
    }

    public function getAcknowledge(): Acknowledge
    {
        return $this->acknowledge;
    }
}