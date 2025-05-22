<?php

namespace App\Events\Depot;

use App\Entity\Depot;
use Symfony\Contracts\EventDispatcher\Event;

class DepotCreatedEvent extends Event
{
    const NAME = 'app.event.depot.created';
    protected $depot;

    public function __construct(Depot $depot)
    {
        $this->depot = $depot;
    }

    public function getDepot(): Depot
    {
        return $this->depot;
    }
}