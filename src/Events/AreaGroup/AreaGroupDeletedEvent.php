<?php

namespace App\Events\AreaGroup;

use App\Entity\User;
use App\Entity\AreaGroup;
use Symfony\Contracts\EventDispatcher\Event;

class AreaGroupDeletedEvent extends Event
{
    const NAME = 'app.event.areaGroup.deleted';
    protected $areaGroup;
    protected $user;

    public function __construct(AreaGroup $areaGroup, User $user)
    {
        $this->areaGroup = $areaGroup;
        $this->user = $user;
    }

    public function getAreaGroup(): AreaGroup
    {
        return $this->areaGroup;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}