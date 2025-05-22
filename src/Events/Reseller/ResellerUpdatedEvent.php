<?php

namespace App\Events\Reseller;

use App\Entity\Reseller;
use Symfony\Contracts\EventDispatcher\Event;

class ResellerUpdatedEvent extends Event
{
    public const NAME = 'app.event.reseller.updated';
    protected $reseller;

    public function __construct(Reseller $reseller)
    {
        $this->reseller = $reseller;
    }

    public function getReseller(): Reseller
    {
        return $this->reseller;
    }
}