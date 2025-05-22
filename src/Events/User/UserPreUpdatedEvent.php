<?php

namespace App\Events\User;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserPreUpdatedEvent extends Event
{
    const NAME = 'app.event.user.preUpdated';
    protected $user;
    protected $oldDriverSensorId;
    protected $newDriverSensorId;
    protected $isDriverSensorChanged = false;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return mixed
     */
    public function getOldDriverSensorId()
    {
        return $this->oldDriverSensorId;
    }

    /**
     * @param mixed $oldDriverSensorId
     */
    public function setOldDriverSensorId($oldDriverSensorId): void
    {
        $this->oldDriverSensorId = $oldDriverSensorId;
    }

    /**
     * @return mixed
     */
    public function getNewDriverSensorId()
    {
        return $this->newDriverSensorId;
    }

    /**
     * @param mixed $newDriverSensorId
     */
    public function setNewDriverSensorId($newDriverSensorId): void
    {
        $this->newDriverSensorId = $newDriverSensorId;
    }

    /**
     * @return bool
     */
    public function isDriverSensorChanged(): bool
    {
        return $this->isDriverSensorChanged;
    }

    /**
     * @param bool $isDriverSensorChanged
     */
    public function setIsDriverSensorChanged(bool $isDriverSensorChanged): void
    {
        $this->isDriverSensorChanged = $isDriverSensorChanged;
    }
}