<?php

namespace App\Events\ServiceRecord;

use App\Entity\ServiceRecord;
use Symfony\Contracts\EventDispatcher\Event;

class ServiceRecordDeletedEvent extends Event
{
    const NAME = 'app.event.serviceRecord.deleted';
    protected $serviceRecord;

    public function __construct(ServiceRecord $serviceRecord)
    {
        $this->serviceRecord = $serviceRecord;
    }

    public function getServiceRecord(): ServiceRecord
    {
        return $this->serviceRecord;
    }
}