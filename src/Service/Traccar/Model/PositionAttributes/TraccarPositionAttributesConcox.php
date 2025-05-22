<?php

namespace App\Service\Traccar\Model\PositionAttributes;

/**
 * @example {"sat": 9, "ignition": true, "odometer": 0, "distance": 59.68, "totalDistance": 15207731.7, "motion": false, "hours": -107000}
 * @example {"iccid":"8910390000013276670","distance":0.0,"totalDistance":5.543769981E7,"motion":false,"hours":1645357064000}
 * @example {"status":68,"ignition":false,"charge":true,"blocked":false,"batteryLevel":100,"rssi":2,"distance":0.0,"totalDistance":5.543769981E7,"motion":false,"hours":1645357064000}
 */
class TraccarPositionAttributesConcox extends TraccarPositionAttributes
{
    /**
     * @param \stdClass|array $fields
     */
    public function __construct($fields)
    {
        parent::__construct($fields);
    }
}

