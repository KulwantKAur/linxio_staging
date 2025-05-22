<?php

namespace App\Events\Device;

use App\Entity\Device;
use App\Entity\DeviceInstallation;
use App\Entity\User;
use App\Entity\Vehicle;
use Symfony\Contracts\EventDispatcher\Event;

class DeviceUninstalledEvent extends Event
{
    const NAME = 'app.event.device.uninstalled';
    protected ?DeviceInstallation $deviceInstallation;
    protected User $user;

    public function __construct(?DeviceInstallation $deviceInstallation, User $user)
    {
        $this->deviceInstallation = $deviceInstallation;
        $this->user = $user;
    }

    public function getDevice(): Device
    {
        return $this->deviceInstallation->getDevice();
    }

    public function getVehicle(): Vehicle
    {
        return $this->deviceInstallation->getVehicle();
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getDeviceInstallation(): ?DeviceInstallation
    {
        return $this->deviceInstallation;
    }
}