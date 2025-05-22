<?php

namespace App\Events\Depot;

use App\Entity\Depot;
use Symfony\Contracts\EventDispatcher\Event;

class DepotDeletedEvent extends Event
{
    const NAME = 'app.event.depot.deleted';
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