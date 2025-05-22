<?php

namespace App\EventListener\DeviceSensor;

use App\Entity\DeviceSensor;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Psr\Log\LoggerInterface;

class DeviceSensorEntityListener
{
    private $logger;

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * @param DeviceSensor $deviceSensor
     * @param PreUpdateEventArgs $event
     * @throws \Exception
     */
    public function preUpdate(DeviceSensor $deviceSensor, PreUpdateEventArgs $event)
    {
        if ($event->hasChangedField('lastTrackerHistorySensor') && $deviceSensor->hasAsset()) {
            $this->logger->info('before DeviceSensorEntityListener->preUpdate', ['device' => $deviceSensor->getDevice()->getId()]);
            $asset = $deviceSensor->getAsset();
            $assetLastSensorHistory = $asset->getLastTrackerHistorySensor();
            $deviceLastSensorHistory = $deviceSensor->getLastTrackerHistorySensor();

            if (!$assetLastSensorHistory && $deviceLastSensorHistory) {
                $asset->setLastTrackerHistorySensor($deviceLastSensorHistory);
            } else {
                if (
                    $deviceLastSensorHistory
                    && $assetLastSensorHistory
                    && $deviceLastSensorHistory->getOccurredAt() > $assetLastSensorHistory->getOccurredAt()
                ) {
                    $asset->setLastTrackerHistorySensor($deviceLastSensorHistory);
                }
            }
            $this->logger->info('after DeviceSensorEntityListener->preUpdate', ['device' => $deviceSensor->getDevice()->getId()]);
        }
    }
}