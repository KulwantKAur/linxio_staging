<?php

namespace App\Events\Route;

use App\Entity\Route;
use Symfony\Contracts\EventDispatcher\Event;

class RouteUpdatedEvent extends Event
{
    const NAME = 'app.event.route.updated';
    protected $route;

    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    public function getRoute(): Route
    {
        return $this->route;
    }
}