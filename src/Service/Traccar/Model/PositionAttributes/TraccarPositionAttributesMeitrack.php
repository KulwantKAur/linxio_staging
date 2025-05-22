<?php

namespace App\Service\Traccar\Model\PositionAttributes;

/**
 * @link https://www.meitrack.com/cd-download/Protocols/MEITRACK_GPRS_Protocol.pdf
 * @link https://www.meitrack.com/cd-download/Protocols/MEITRACK_P99G_GPRS_Protocol.pdf
 * @example {"event":35,"sat":9,"hdop":5,"odometer":4096,"runtime":"32648","input":0,"output":0,"adc1":0,"adc2":0,"adc3":0,"battery":1023,"power":768,"distance":0,"totalDistance":0,"motion":true}
 * @example SOS (event 1):
 * {"position":{"id":0,"attributes":{"sat":0,"rssi":0,"hdop":0,"battery":3.97,"power":13.790000000000001,"event":1,"odometer":75115,"runtime":177622,"distance":0,"totalDistance":6356795.83,"motion":false},"deviceId":5,"type":null,"protocol":"meitrack","serverTime":"2022-02-07T08:10:15.992+00:00","deviceTime":"2021-12-20T11:12:19.000+00:00","fixTime":"2021-12-20T11:12:19.000+00:00","outdated":false,"valid":false,"latitude":26.304076,"longitude":50.203481,"altitude":0,"speed":0,"course":0,"address":null,"accuracy":0,"network":null},"device":{"id":5,"attributes":[],"groupId":0,"name":"Meitrack: 861585043200862","uniqueId":"861585043200862","status":"online","lastUpdate":"2022-02-07T08:10:15.992+00:00","positionId":1657,"geofenceIds":[],"phone":"","model":"","contact":"","category":null,"disabled":false}}
 */
class TraccarPositionAttributesMeitrack extends TraccarPositionAttributes
{
    public CONST EVENT_SOS_ID = 1;

    /** @var int|null */
    private $runtime;
    /** @var int|null */
    private $hdop;
    /** @var bool|null */
    private $input;
    /** @var bool|null */
    private $output;
    /** @var bool|null */
    private $adc1;
    /** @var bool|null */
    private $adc2;
    /** @var bool|null */
    private $adc3;
    /** @var int|null */
    private $event;

    /**
     * @param \stdClass|array $fields
     */
    public function __construct($fields)
    {
        parent::__construct($fields);
        $this->hdop = $fields->hdop ?? null;
        $this->runtime = $fields->runtime ?? null;
        $this->input = $fields->input ?? null;
        $this->output = $fields->output ?? null;
        $this->adc1 = $fields->adc1 ?? null;
        $this->adc2 = $fields->adc2 ?? null;
        $this->adc3 = $fields->adc3 ?? null;
        $this->event = $fields->event ?? null;
    }

    /**
     * @return int|null
     */
    public function getRuntime(): ?int
    {
        return $this->runtime;
    }

    /**
     * @return int|null
     */
    public function getEvent(): ?int
    {
        return $this->event;
    }
}

