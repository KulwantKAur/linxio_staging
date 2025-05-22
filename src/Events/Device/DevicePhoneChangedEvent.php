<?php

namespace App\Events\Device;

use App\Entity\Device;
use Symfony\Contracts\EventDispatcher\Event;

class DevicePhoneChangedEvent extends Event
{
    const NAME = 'app.event.device.phone.changed';
    protected $device;

    public function __construct(Device $device)
    {
        $this->device = $device;
    }

    public function getDevice(): Device
    {
        return $this->device;
    }
}