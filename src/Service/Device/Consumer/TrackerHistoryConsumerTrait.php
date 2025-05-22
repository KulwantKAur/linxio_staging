<?php

namespace App\Service\Device\Consumer;

use App\Entity\Device;
use App\Entity\EventLog\EventLog;
use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use App\Entity\Setting;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Vehicle;
use Carbon\Carbon;

trait TrackerHistoryConsumerTrait
{
    public bool $isActionIgnoreStatus = false;

    /**
     * @param array $thObjectsArray
     * @return array|null
     */
    protected function formatTHObjectsArray(array $thObjectsArray): ?array
    {
        return array_map(function (\stdClass $thObject) {
            return [
                'id' => $thObject->id,
                'ts' => $thObject->tsISO8601 ?? null,
                'createdAt' => $thObject->createdAt ?? null,
                'ignition' => $thObject->ignition ?? null,
                'movement' => $thObject->movement ?? null,
                'driverId' => $thObject->driverId ?? null,
                'odometer' => $thObject->mileageFromTracker ?? null,
                'speed' => $thObject->speed ?? null,
                'lng' => $thObject->lng ?? null,
                'lat' => $thObject->lat ?? null,
                'batteryVoltagePercentage' => $thObject->batteryVoltagePercentage ?? null,
                'deviceId' => $thObject->deviceId ?? null,
                'lastCoordinates' => $thObject->lastCoordinates ?? null,
                'address' => $thObject->address ?? null,
                'angle' => $thObject->angle ?? null,
                'lastDataReceived' => $thObject->lastDataReceived ?? null,
                'temperatureLevel' => $thObject->temperatureLevel ?? null,
                'mileage' => $thObject->mileage ?? null,
                'engineHours' => $thObject->engineHours ?? null,
                'batteryVoltage' => $thObject->batteryVoltage ?? null,
                'externalVoltage' => $thObject->externalVoltage ?? null,
                'standsIgnition' => $thObject->standsIgnition ?? null,
                'iButton' => $thObject->iButton ?? null,
                'engineOnTime' => $thObject->engineOnTime ?? null,
            ];
        }, $thObjectsArray);
    }

    protected function formatTHToArray(TrackerHistory $th): ?array
    {
        return [
            'id' => $th->getId(),
            'ts' => $th->getTs()->format('c'),
            'createdAt' => $th->getCreatedAt()->format('c'),
            'ignition' => $th->getIgnition() ?? null,
            'movement' => $th->getMovement() ?? null,
            'driverId' => $th->getDriver()?->getId() ?? null,
            'odometer' => $th->getOdometer() ?? null,
            'speed' => $th->getSpeed() ?? null,
            'lng' => $th->getLng() ?? null,
            'lat' => $th->getLat() ?? null,
        ];
    }

    /**
     * @param TrackerHistory $th
     * @return array
     * @throws \Exception
     */
    private function makeTrackerHistoryData(TrackerHistory $th): array
    {
        $trackerData = $th->toArray(MessageHelper::getTHFields());
        $trackerData['odometer'] = $trackerData['mileageFromTracker'];
        $trackerData['ts'] = $trackerData['tsISO8601'];

        return $trackerData;
    }

    private function isValidToTriggerEvent(string $thTs, string $thCreatedAt): bool
    {
        if (!$thTs || !$thCreatedAt) {
            return false;
        }
        $tsDiff = Carbon::parse($thCreatedAt)->getTimestamp() - Carbon::parse($thTs)->getTimestamp();

        return $tsDiff < 60 * 60 * 24;
    }

    public function setRedisKey(string $tag, Event $event, Device $device, Notification $ntf): void
    {
        $this->redisKey = $tag . '-' . 'eventId-' . $event->getId()
            . 'deviceId-' . $device->getId() . 'ntfId-' . $ntf->getId();
    }

    private function getStatus(array $trackerData, string $tag): string
    {
        $status = TrackerHistory::getDeviceStatusByIgnitionAndMovement(
            $trackerData['ignition'],
            $trackerData['movement']
        );

        switch ($status) {
            case Device::STATUS_DRIVING:
            case Device::STATUS_IDLE:
                if ($this->cacheData) {
                    if (!empty($this->cacheData['stopTime'])
                        && ($this->getStopDuration($trackerData['ts'], $this->cacheData['stopTime'])
                            > self::IGNORE_STOPS)
                    ) {
                        // delete the cache to start counting again for notifications
                        if ($this->memoryDb->deleteItem($this->redisKey)) {
                            $this->cacheData = [];
                        }

                        return Device::STATUS_STOPPED;
                    }

                    $this->updateCache(['stopTime' => null], $tag);
                }

                return Device::STATUS_DRIVING;
            case Device::STATUS_STOPPED:
                // save the time of the first stop in order to calculate the duration of the stop in the future
                if ($this->cacheData && empty($this->cacheData['stopTime'])) {
                    $this->updateCache(['stopTime' => $trackerData['ts']], $tag);
                }

                if ($this->cacheData
                    && !empty($this->cacheData['stopTime'])
                    && ($this->getStopDuration($trackerData['ts'], $this->cacheData['stopTime']) < self::IGNORE_STOPS)
                    //handle the same and wrong ts
                    && ($this->getStopDuration($trackerData['ts'], $this->cacheData['stopTime']) > 0)
                ) {
                    return Device::STATUS_DRIVING;
                }

                return Device::STATUS_STOPPED;
            default:
                return $status;
        }
    }

    private function updateCache(array $updateCache, string $tag): array
    {
        foreach ($updateCache as $key => $value) {
            if (array_key_exists($key, $this->cacheData)) {
                $this->cacheData[$key] = $value;
            }
        }

        if (!$this->cacheData) {
            if ($this->memoryDb->deleteItem($this->redisKey)) {
                $this->cacheData = [];
            }
        } else {
            $this->memoryDb->setToJsonTtl(
                $this->redisKey,
                $this->cacheData,
                $tag,
                self::MAX_TTL
            );
        }

        return $this->cacheData;
    }

    /**
     * @param Notification $ntf
     * @param Event $event
     * @param Device $device
     * @param Vehicle $vehicle
     * @param array $trackerData
     * @param string|null $status
     * @param bool $isTrigger
     * @return array
     */
    private function setDataCache(
        Notification $ntf,
        Event $event,
        Device $device,
        Vehicle $vehicle,
        array $trackerData,
        ?string $status,
        bool $isTrigger = false
    ): array {
        return [
            'notificationId' => $ntf->getId() ?? null,
            'eventId' => $event->getId() ?? null,
            'vehicleId' => $vehicle->getId() ?? null,
            'deviceId' => $device->getId() ?? null,
            'teamId' => $device->getTeam()->getId() ?? null,
            'ts' => $trackerData['ts'] ?? null,
            'trackerHistoryCreatedAt' => $trackerData['createdAt'] ?? null,
            'thId' => $trackerData['id'] ?? null,
            'status' => $status ?? null,
            'isTrigger' => $isTrigger,
            'stopTime' => null,
            'odometer' => $trackerData['odometer'] ?? null,
            'speed' => $trackerData['speed'] ?? null
        ];
    }

    /**
     * @param string $dateNow
     * @param string $dateStop
     * @return int
     */
    public function getStopDuration(string $dateNow, string $dateStop): int
    {
        return Carbon::parse($dateNow)->getTimestamp() - Carbon::parse($dateStop)->getTimestamp();
    }

    /**
     * @param string $dateNow
     * @param string $datePrev
     * @return int
     */
    public function getDrivingDuration(string $dateNow, string $datePrev): int
    {
        return Carbon::parse($dateNow)->getTimestamp() - Carbon::parse($datePrev)->getTimestamp();
    }

    private function getDuration(
        Notification $ntf,
        array $trackerData,
        string $status,
        string $typeAction = Device::STATUS_DRIVING
    ): ?int {
        $timeDurationNtf = $ntf->getAdditionalParams()[Notification::TIME_DURATION] ?? null;

        if ($trackerData['id']
            && $this->isStatusCheck($status, $typeAction)
            && $trackerData['ts'] && $this->cacheData['ts']
            && ($this->getDrivingDuration($trackerData['ts'], $this->cacheData['ts']) >= $timeDurationNtf)
            && (!$this->cacheData['isTrigger'])
        ) {
            return $this->getDrivingDuration($trackerData['ts'], $this->cacheData['ts']);
        }

        return null;
    }

    /**
     * @param Notification $ntf
     * @param array $trackerData
     * @param string $status
     * @param string $typeAction
     * @return int|null
     */
    private function getDistance(
        Notification $ntf,
        array $trackerData,
        string $status,
        string $typeAction = Device::STATUS_DRIVING
    ): ?int {
        $distanceNtf = $ntf->getAdditionalParams()[Notification::DISTANCE] ?? null;

        if ($trackerData['id']
            && $this->isStatusCheck($status, $typeAction)
            && $trackerData['odometer'] && $this->cacheData['odometer']
            && ($trackerData['odometer'] - $this->cacheData['odometer']) >= $distanceNtf
            && (!$this->cacheData['isTrigger'])
        ) {
            return $trackerData['odometer'] - $this->cacheData['odometer'];
        }

        return null;
    }


    /**
     * @param string $status
     * @param string $typeAction
     * @return bool
     */
    public function isStatusCheck(string $status, string $typeAction): bool
    {
        return ($status === $typeAction || $this->isActionIgnoreStatus());
    }

    /**
     * If you want to ignore the movement status of the vehicle, then set 'true'
     * @return bool
     */
    public function isActionIgnoreStatus(): bool
    {
        return $this->isActionIgnoreStatus;
    }

    private function getDistanceForContext(array $trackerData): ?int
    {
        if ($trackerData['odometer'] && $this->cacheData['odometer']
            && ($trackerData['odometer'] - $this->cacheData['odometer']) >= 0
        ) {
            return $trackerData['odometer'] - $this->cacheData['odometer'];
        }

        return null;
    }

    private function getDurationForContext(array $trackerData): ?int
    {
        if ($trackerData['ts'] && $this->cacheData['ts']
            && ($this->getDrivingDuration($trackerData['ts'], $this->cacheData['ts']) >= 0)
        ) {
            return $this->getDrivingDuration($trackerData['ts'], $this->cacheData['ts']);
        }

        return null;
    }

    private function triggerDeviceTts(Device $device, string $text, $logger = null)
    {
        $setting = $device->getTeam()->getSettingsByName(Setting::DEVICE_TTS)?->getValue() ?? false;
        $this->logger->error('1.triggerDeviceTts team id and setting value ' . $device->getTeamId() . ' ' . (int)$setting);
        if ($setting) {
            $this->logger->error('2.triggerDeviceTts send text ' . $device->getId() . ' ' . $text);
            $result = $this->deviceService->sendTTSToDevice($device, $text);
            $this->logger->error('3.triggerDeviceTts result ' . (int)$result);
        }
    }

    private function triggerOverspeedingNtf(
        TrackerHistory $trackerHistory,
        Notification $ntf,
                       $speedLimit = null,
                       $duration = null,
                       $distance = null
    ) {
        $lat = $trackerHistory->getLat() ?? null;
        $lng = $trackerHistory->getLng() ?? null;
        $address = ($lat && $lng) ? $this->mapService->getLocationByCoordinates($lat, $lng) : null;
        $context = [
            EventLog::ADDRESS => $address,
            EventLog::LAT => $lat,
            EventLog::LNG => $lng,
            EventLog::DURATION => $duration ?? 0,
            EventLog::DISTANCE => $distance ?? 0,
            'notificationId' => $ntf->getId(),
            'prevTHId' => $this->cacheData['thId'],
            'cacheData' => $this->cacheData,
            'speedLimit' => $speedLimit
        ];
        // update the trigger value in the cache to not send notifications until the flag is cleared
        $this->cacheData = $this->updateCache(['isTrigger' => true], self::TAG_EXCEEDING_SPEED_LIMIT);
        $this->notificationDispatcher->dispatch(
            Event::EXCEEDING_SPEED_LIMIT,
            $trackerHistory,
            $trackerHistory->getTs(),
            $context
        );
    }
}
