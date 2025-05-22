<?php

namespace App\Events\Area;

use App\Entity\Area;
use Symfony\Contracts\EventDispatcher\Event;

class AreaDeletedEvent extends Event
{
    const NAME = 'app.event.area.deleted';
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