<?php

namespace App\Service\Traccar\Model\PositionAttributes;

/**
 * @example {"priority":0,"sat":13,"event":240,"ignition":false,"motion":true,"io200":0,"io113":0,"power":12.316,"io24":0,"battery":0,"io68":0,"io13":1715,"io15":83,"operator":25701,"tripOdometer":0,"odometer":6896473,"io12":1129192,"io238":0,"distance":5025.24,"totalDistance":13027485.82,"hours":737000}
 * @example db: {"IOData":{"239":1,"240":0,"80":1,"21":5,"200":0,"69":1,"30":1,"37":0,"181":9,"182":5,"66":12899,"24":0,"67":3963,"68":8,"49":17976,"16":1201945025}}
 */
class TraccarPositionAttributesTeltonika extends TraccarPositionAttributes
{
    private const IO_KEY_PREFIX = 'io';

    /** @var array|null */
    private $IOData;

    /**
     * @param \stdClass $fields
     * @return array
     */
    private function handleIOData(\stdClass $fields): array
    {
        $dataArray = get_object_vars($fields);
        $dataIOArray = [];

        foreach ($dataArray as $key => $value) {
            $key = substr($key, 0, 2) == self::IO_KEY_PREFIX ? intval(substr($key, 2)) : null;

            if ($key) {
                $dataIOArray[$key] = $value;
            }
        }

        return $dataIOArray;
    }

    /**
     * @param \stdClass|array $fields
     */
    public function __construct($fields)
    {
        parent::__construct($fields);
        $this->IOData = $this->handleIOData($fields);
    }

    /**
     * @return array|null
     */
    public function getIOData(): ?array
    {
        return $this->IOData;
    }
}

