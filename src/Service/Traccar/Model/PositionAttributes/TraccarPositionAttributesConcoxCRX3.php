<?php

namespace App\Service\Traccar\Model\PositionAttributes;

/**
 * @example {"adc1":12.4,"distance":0,"totalDistance":68961.77202893827,"motion":false,"batteryLevel":100,"hours":63193000}"
 * @example {"sat":6,"ignition":true,"event":0,"archive":false,"odometer":61,"distance":3.8339280359407093,"totalDistance":68946.04382451002,"motion":true,"batteryLevel":100,"hours":63152000}"
 */
class TraccarPositionAttributesConcoxCRX3 extends TraccarPositionAttributesConcox
{
    /**
     * @param \stdClass|array $fields
     */
    public function __construct($fields)
    {
        parent::__construct($fields);
        $this->odometer = isset($fields->totalDistance) ? intval($fields->totalDistance) : null;
        $this->power = $fields->adc1 ?? null;
        // @todo add logic for ignition if speed=0 & motion=0 then ignition=1 ?
    }
}

