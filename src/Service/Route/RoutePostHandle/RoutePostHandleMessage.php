<?php

namespace App\Service\Route\RoutePostHandle;

class RoutePostHandleMessage
{
    private $deviceId;
    private $dateFrom;
    private $dateTo;

    /**
     * @param int $deviceId
     * @param string $dateFrom
     * @param string $dateTo
     */
    public function __construct(
        int $deviceId,
        string $dateFrom,
        string $dateTo
    ) {
        $this->deviceId = $deviceId;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    /**
     * @return false|string
     */
    public function __toString()
    {
        return json_encode([
            'device_id' => $this->getDeviceId(),
            'date_from' => $this->getDateFrom(),
            'date_to' => $this->getDateTo(),
        ]);
    }

    /**
     * @return int
     */
    public function getDeviceId(): int
    {
        return $this->deviceId;
    }

    /**
     * @return string
     */
    public function getDateFrom(): string
    {
        return $this->dateFrom;
    }

    /**
     * @return string
     */
    public function getDateTo(): string
    {
        return $this->dateTo;
    }
}
