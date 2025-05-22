<?php

namespace App\Events\Repair;

use App\Entity\ServiceRecord;
use Symfony\Contracts\EventDispatcher\Event;

class RepairDeletedEvent extends Event
{
    const NAME = 'app.event.repair.deleted';
    protected $serviceRecord;

    public function __construct(ServiceRecord $serviceRecord)
    {
        $this->serviceRecord = $serviceRecord;
    }

    public function getRepair(): ServiceRecord
    {
        return $this->serviceRecord;
    }
}