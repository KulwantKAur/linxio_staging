<?php

namespace App\Events\Asset;

use App\Entity\Asset;
use Symfony\Contracts\EventDispatcher\Event;

class AssetUpdatedEvent extends Event
{
    public const NAME = 'app.event.asset.updated';
    protected $asset;

    public function __construct(Asset $asset)
    {
        $this->asset = $asset;
    }

    public function getAsset(): Asset
    {
        return $this->asset;
    }
}