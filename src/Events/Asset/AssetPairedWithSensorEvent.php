<?php

namespace App\Events\Asset;

use App\Entity\Asset;
use App\Entity\Sensor;
use Symfony\Contracts\EventDispatcher\Event;

class AssetPairedWithSensorEvent extends Event
{
    public const NAME = 'app.event.asset.pairedWithSensor';
    protected $asset;
    protected $sensor;

    /**
     * AssetPairedWithSensorEvent constructor.
     * @param Asset $asset
     * @param Sensor $sensor
     */
    public function __construct(Asset $asset, Sensor $sensor)
    {
        $this->asset = $asset;
        $this->sensor = $sensor;
    }

    /**
     * @return Asset
     */
    public function getAsset(): Asset
    {
        return $this->asset;
    }

    /**
     * @return Sensor
     */
    public function getSensor(): Sensor
    {
        return $this->sensor;
    }
}