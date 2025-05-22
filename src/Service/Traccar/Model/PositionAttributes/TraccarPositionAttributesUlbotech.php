<?php

namespace App\Service\Traccar\Model\PositionAttributes;

/**
 * @todo
 * @example {"result":"GPS:3;N25.305037;E55.379674;2;355;2.10,STT:C202;0,MGR:15652025,ADC:0;13.43;1;43.57;2;4.12,EVT:F0;200","distance":0,"totalDistance":0,"motion":false}
 */
class TraccarPositionAttributesUlbotech extends TraccarPositionAttributes
{
    /**
     * @param \stdClass $fields
     */
    public function __construct(\stdClass $fields)
    {
        parent::__construct($fields);
    }
}

