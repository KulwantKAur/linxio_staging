<?php

namespace App\Report\Core\DTO;

use Carbon\Carbon;

class TempRouteDTO
{
    public $minTs;
    public $maxTs;
    public ?int $minOdometer;
    public ?int $maxOdometer;
    public $maxSpeed;
    public int $duration;
    public int $distance;

    public function __construct(array $data)
    {
        $this->minTs = $data['min_ts'] ?? null;
        $this->maxTs = $data['max_ts'] ?? null;
        $this->minOdometer = $data['min_odometer'] ? (int)$data['min_odometer'] : null;
        $this->maxOdometer = $data['max_odometer'] ? (int)$data['max_odometer'] : null;
        $this->maxSpeed = (int)$data['max_speed'];
        $this->distance = (int)$data['distance'];
        $this->duration = $this->minTs && $this->maxTs
            ? (new Carbon($this->maxTs))->diffInSeconds(new Carbon($this->minTs)) : 0;
    }
}