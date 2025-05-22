<?php

namespace App\Service\Traccar\Model;

use App\Entity\Device;
use App\Service\Traccar\Model\EventAttributes\TraccarEventAttributes;

/**
 * @internal https://github.com/traccar/traccar/blob/master/src/main/java/org/traccar/model/Event.java
 * @example "event":{"id":2261,"attributes":{"result":"GPS:3;N25.305037;E55.379674;2;355;2.10,STT:C202;0,MGR:15652025,ADC:0;13.43;1;43.57;2;4.12,EVT:F0;200"},"deviceId":3,"type":"commandResult","eventTime":"2021-08-11T09:04:33.305+00:00","positionId":5386,"geofenceId":0,"maintenanceId":0}
 * @example "event":{"id":2353,"attributes":[],"deviceId":4,"type":"deviceOnline","eventTime":"2021-08-12T11:30:35.648+00:00","positionId":0,"geofenceId":0,"maintenanceId":0}
 * @example "event":{"id":2354,"attributes":[],"deviceId":4,"type":"deviceOffline","eventTime":"2021-08-12T11:30:36.321+00:00","positionId":0,"geofenceId":0,"maintenanceId":0}
 * @example alarm (SOS) event:
 * {"event":{"id":173,"attributes":{"alarm":"sos"},"deviceId":5,"type":"alarm","eventTime":"2022-02-07T08:10:15.992+00:00","positionId":0,"geofenceId":0,"maintenanceId":0},"device":{"id":5,"attributes":[],"groupId":0,"name":"Meitrack: 861585043200862","uniqueId":"861585043200862","status":"online","lastUpdate":"2022-02-07T08:10:15.992+00:00","positionId":1420,"geofenceIds":[],"phone":"","model":"","contact":"","category":null,"disabled":false},"users":[{"id":1,"attributes":[],"name":"admin","login":"","email":"admin","phone":"","readonly":false,"administrator":true,"map":"","latitude":0,"longitude":0,"zoom":0,"twelveHourFormat":false,"coordinateFormat":"","disabled":false,"expirationTime":null,"deviceLimit":-1,"userLimit":0,"deviceReadonly":false,"token":null,"limitCommands":false,"poiLayer":"","password":null}]}
 */

class TraccarEvent extends TraccarModel
{
    public const ALL_EVENTS_TYPE = "allEvents";
    public const DEVICE_COMMAND_RESULT_TYPE = 'commandResult';    
    public const DEVICE_ONLINE_TYPE = 'deviceOnline';
    public const DEVICE_OFFLINE_TYPE = 'deviceOffline';
    public const DEVICE_UNKNOWN_TYPE = 'deviceUnknown';
    public const DEVICE_INACTIVE_TYPE = 'deviceInactive';
    public const DEVICE_MOVING_TYPE = 'deviceMoving';
    public const DEVICE_STOPPED_TYPE = 'deviceStopped';
    public const DEVICE_OVERSPEED_TYPE = 'deviceOverspeed';
    public const DEVICE_FUEL_DROP_TYPE = 'deviceFuelDrop';
    public const GEOFENCE_ENTER_TYPE = 'geofenceEnter';
    public const GEOFENCE_EXIT_TYPE = 'geofenceExit';
    public const ALARM_TYPE = 'alarm';
    public const IGNITION_ON_TYPE = 'ignitionOn';
    public const IGNITION_OFF_TYPE = 'ignitionOff';
    public const MAINTENANCE_TYPE = 'maintenance';
    public const TEXT_MESSAGE_TYPE = 'textMessage';
    public const DRIVER_CHANGED_TYPE = 'driverChanged';

    public const ALARM_GENERAL = 'general';
    public const ALARM_SOS = 'sos';
    public const ALARM_VIBRATION = 'vibration';
    public const ALARM_MOVEMENT = 'movement';
    public const ALARM_LOW_SPEED = 'lowspeed';
    public const ALARM_OVERSPEED = 'overspeed';
    public const ALARM_FALL_DOWN = 'fallDown';
    public const ALARM_LOW_POWER = 'lowPower';
    public const ALARM_LOW_BATTERY = 'lowBattery';
    public const ALARM_FAULT = 'fault';
    public const ALARM_POWER_OFF = 'powerOff';
    public const ALARM_POWER_ON = 'powerOn';
    public const ALARM_DOOR = 'door';
    public const ALARM_LOCK = 'lock';
    public const ALARM_UNLOCK = 'unlock';
    public const ALARM_GEOFENCE = 'geofence';
    public const ALARM_GEOFENCE_ENTER = 'geofenceEnter';
    public const ALARM_GEOFENCE_EXIT = 'geofenceExit';
    public const ALARM_GPS_ANTENNA_CUT = 'gpsAntennaCut';
    public const ALARM_ACCIDENT = 'accident';
    public const ALARM_TOW = 'tow';
    public const ALARM_IDLE = 'idle';
    public const ALARM_HIGH_RPM = 'highRpm';
    public const ALARM_ACCELERATION = 'hardAcceleration';
    public const ALARM_BRAKING = 'hardBraking';
    public const ALARM_CORNERING = 'hardCornering';
    public const ALARM_LANE_CHANGE = 'laneChange';
    public const ALARM_FATIGUE_DRIVING = 'fatigueDriving';
    public const ALARM_POWER_CUT = 'powerCut';
    public const ALARM_POWER_RESTORED = 'powerRestored';
    public const ALARM_JAMMING = 'jamming';
    public const ALARM_TEMPERATURE = 'temperature';
    public const ALARM_PARKING = 'parking';
    public const ALARM_SHOCK = 'shock';
    public const ALARM_BONNET = 'bonnet';
    public const ALARM_FOOT_BRAKE = 'footBrake';
    public const ALARM_FUEL_LEAK = 'fuelLeak';
    public const ALARM_TAMPERING = 'tampering';
    public const ALARM_REMOVING = 'removing';

    /** @var int|null $id */
    public $id;
    /** @var string $type */
    public $type;
    /** @var int $deviceId */
    public $deviceId;
    /** @var int|null $positionId */
    public $positionId;
    /** @var \DateTimeInterface $eventTime */
    public $eventTime;
    /** @var \stdClass|null $rawAttributes */
    public $rawAttributes;
    /** @var TraccarEventAttributes|null $attributes */
    public $attributes;
    /** @var int|null $maintenanceId */
    public $maintenanceId;
    /** @var int|null $geofenceId */
    public $geofenceId;

    /**
     * @param \stdClass|array $fields
     * @param Device|null $device
     * @throws \Exception
     */
    public function __construct($fields, ?Device $device)
    {
        $fields = self::convertArrayToObject($fields);
        $this->id = $this->handlePossibleZeroValueInRawField($fields->id);
        $this->deviceId = $fields->deviceId ?? null;
        $this->type = $fields->type ?? null;
        $this->positionId = $this->handlePossibleZeroValueInRawField($fields->positionId);
        $this->maintenanceId = $this->handlePossibleZeroValueInRawField($fields->maintenanceId);
        $this->geofenceId = $this->handlePossibleZeroValueInRawField($fields->geofenceId);
        $this->eventTime = isset($fields->eventTime) ? $this->convertDeviceDateToDatetime($fields->eventTime) : null;
        $this->rawAttributes = isset($fields->attributes) ? self::convertArrayToObject($fields->attributes) : null;
        $this->attributes = $this->handleEventAttributes($this, $device);
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getEventTime(): \DateTimeInterface
    {
        return $this->eventTime;
    }

    /**
     * @param \DateTimeInterface $eventTime
     * @return TraccarEvent
     */
    public function setEventTime(\DateTimeInterface $eventTime): TraccarEvent
    {
        $this->eventTime = $eventTime;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPositionId(): ?int
    {
        return $this->positionId;
    }

    /**
     * @param int|null $positionId
     * @return TraccarEvent
     */
    public function setPositionId(?int $positionId): TraccarEvent
    {
        $this->positionId = ($positionId == 0) ? null : $positionId;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return TraccarEvent
     */
    public function setType(string $type): TraccarEvent
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getGeofenceId(): ?int
    {
        return $this->geofenceId;
    }

    /**
     * @param int|null $geofenceId
     * @return TraccarEvent
     */
    public function setGeofenceId(?int $geofenceId): TraccarEvent
    {
        $this->geofenceId = $geofenceId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getProtocol(): ?string
    {
        return null;
    }

    /**
     * @return \stdClass|null
     */
    public function getRawAttributes(): ?\stdClass
    {
        return $this->rawAttributes;
    }

    /**
     * @return TraccarEventAttributes|null
     */
    public function getAttributes(): ?TraccarEventAttributes
    {
        return $this->attributes;
    }
}

