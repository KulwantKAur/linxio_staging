<?php

namespace App\Events\Depot;

use App\Entity\Depot;
use Symfony\Contracts\EventDispatcher\Event;

class DepotUpdatedEvent extends Event
{
    const NAME = 'app.event.depot.updated';
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