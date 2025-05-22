<?php

namespace App\Events\Asset;

use App\Entity\Asset;
use Symfony\Contracts\EventDispatcher\Event;

class AssetDeletedEvent extends Event
{
    public const NAME = 'app.event.asset.deleted';
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