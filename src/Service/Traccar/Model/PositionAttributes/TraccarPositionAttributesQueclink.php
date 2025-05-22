<?php

namespace App\Service\Traccar\Model\PositionAttributes;

/**
 * @example {"versionFw":"521","batteryLevel":100,"power":14328,"sat":7,"hdop":0,"distance":0,"totalDistance":0,"motion":true}
 */
class TraccarPositionAttributesQueclink extends TraccarPositionAttributes
{
    /** @var int|null */
    private $hdop;
    /** @var string|null */
    private $versionFw;

    /**
     * @param \stdClass|array $fields
     */
    public function __construct($fields)
    {
        parent::__construct($fields);
        $this->hdop = $fields->hdop ?? null;
        $this->versionFw = $fields->versionFw ?? null;
    }
}

