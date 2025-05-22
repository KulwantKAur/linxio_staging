<?php

namespace App\Events\Area;

use App\Entity\Area;
use Symfony\Contracts\EventDispatcher\Event;

class AreaUpdatedEvent extends Event
{
    const NAME = 'app.event.area.updated';
    protected $area;

    public function __construct(Area $area)
    {
        $this->area = $area;
    }

    public function getArea(): Area
    {
        return $this->area;
    }
}