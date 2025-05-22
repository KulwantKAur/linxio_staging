<?php

namespace App\Service\Traccar\Model\PositionAttributes;

/**
 * @example dmt: {"index": 565, "event": 3, "pdop": 1.2000000000000002, "ignition": true, "input": 3, "output": 0, "status": 3, "battery": 4.125, "deviceTemp": 21.12, "rssi": 24, "solarPower": 4.072, "io6": 7214, "distance": 21255.1, "totalDistance": 42510.2, "motion": true}
 * @example dmthttp: {"index": 130, "event": 9, "ignition": false, "input": 6, "output": 0, "status": 2, "battery": 14.599, "deviceTemp": 27.310000000000002, "rssi": 27, "solarPower": 10.965, "distance": 0, "totalDistance": 0, "motion": false}
 */
class TraccarPositionAttributesDigitalMatter extends TraccarPositionAttributes
{
    private ?int $index;
    private ?int $event;
    private ?float $pdop;
    private ?int $input;
    private ?int $output;
    private ?int $status;
    private ?int $rssi;
    private ?float $solarPower;
    private ?int $io6;

    /**
     * @param \stdClass|array $fields
     */
    public function __construct($fields)
    {
        parent::__construct($fields);
        $this->index = $fields->index ?? null;
        $this->event = $fields->event ?? null;
        $this->pdop = $fields->pdop ?? null;
        $this->input = $fields->input ?? null;
        $this->output = $fields->output ?? null;
        $this->status = $fields->status ?? null;
        $this->rssi = $fields->rssi ?? null;
        $this->solarPower = $fields->solarPower ?? null;
        $this->io6 = $fields->io6 ?? null;
    }
}

