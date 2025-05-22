<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrackerHistoryBackupRepository::class)]
#[ORM\Table(name: 'tracker_history_backup')]
class TrackerHistoryBackup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $trackerPayloadId;

    #[ORM\Column(type: 'datetime')]
    private $ts;

    #[ORM\Column(type: 'float')]
    private $lng;

    #[ORM\Column(type: 'float')]
    private $lat;

    #[ORM\Column(type: 'float')]
    private $alt;

    #[ORM\Column(type: 'float')]
    private $angle;

    #[ORM\Column(type: 'float')]
    private $speed;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\Column(type: 'boolean')]
    private $movement;

    #[ORM\Column(type: 'boolean')]
    private $ignition;

    #[ORM\Column(type: 'float')]
    private $batteryVoltage;

    #[ORM\Column(type: 'float')]
    private $temperatureLevel;

    #[ORM\Column(type: 'integer')]
    private $engineOnTime;

    #[ORM\Column(type: 'integer')]
    private $odometer;

    #[ORM\Column(type: 'string', length: 255)]
    private $deviceId;

    #[ORM\Column(type: 'float')]
    private $externalVoltage;

    #[ORM\Column(type: 'string', length: 255)]
    private $ibutton;

    #[ORM\Column(type: 'boolean')]
    private $isCalculated;

    #[ORM\Column(type: 'boolean')]
    private $isOdometerCorrect;

    #[ORM\Column(type: 'boolean')]
    private $isCalculatedIdling;

    #[ORM\Column(type: 'boolean')]
    private $isCalculatedSpeeding;

    #[ORM\Column(type: 'integer')]
    private $vehicleId;

    // Getters and setters...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTrackerPayloadId(): ?int
    {
        return $this->trackerPayloadId;
    }

    public function setTrackerPayloadId(int $trackerPayloadId): self
    {
        $this->trackerPayloadId = $trackerPayloadId;
        return $this;
    }

    public function getTs(): ?\DateTimeInterface
    {
        return $this->ts;
    }

    public function setTs(\DateTimeInterface $ts): self
    {
        $this->ts = $ts;
        return $this;
    }

    public function getLng(): ?float
    {
        return $this->lng;
    }

    public function setLng(float $lng): self
    {
        $this->lng = $lng;
        return $this;
    }

    public function getLat(): ?float
    {
        return $this->lat;
    }

    public function setLat(float $lat): self
    {
        $this->lat = $lat;
        return $this;
    }

    public function getAlt(): ?float
    {
        return $this->alt;
    }

    public function setAlt(float $alt): self
    {
        $this->alt = $alt;
        return $this;
    }

    public function getAngle(): ?float
    {
        return $this->angle;
    }

    public function setAngle(float $angle): self
    {
        $this->angle = $angle;
        return $this;
    }

    public function getSpeed(): ?float
    {
        return $this->speed;
    }

    public function setSpeed(float $speed): self
    {
        $this->speed = $speed;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getMovement(): ?bool
    {
        return $this->movement;
    }

    public function setMovement(bool $movement): self
    {
        $this->movement = $movement;
        return $this;
    }

    public function getIgnition(): ?bool
    {
        return $this->ignition;
    }

    public function setIgnition(bool $ignition): self
    {
        $this->ignition = $ignition;
        return $this;
    }

    public function getBatteryVoltage(): ?float
    {
        return $this->batteryVoltage;
    }

    public function setBatteryVoltage(float $batteryVoltage): self
    {
        $this->batteryVoltage = $batteryVoltage;
        return $this;
    }

    public function getTemperatureLevel(): ?float
    {
        return $this->temperatureLevel;
    }

    public function setTemperatureLevel(float $temperatureLevel): self
    {
        $this->temperatureLevel = $temperatureLevel;
        return $this;
    }

    public function getEngineOnTime(): ?int
    {
        return $this->engineOnTime;
    }

    public function setEngineOnTime(int $engineOnTime): self
    {
        $this->engineOnTime = $engineOnTime;
        return $this;
    }

    public function getOdometer(): ?int
    {
        return $this->odometer;
    }

    public function setOdometer(int $odometer): self
    {
        $this->odometer = $odometer;
        return $this;
    }

    public function getDeviceId(): ?string
    {
        return $this->deviceId;
    }

    public function setDeviceId(string $deviceId): self
    {
        $this->deviceId = $deviceId;
        return $this;
    }

    public function getExternalVoltage(): ?float
    {
        return $this->externalVoltage;
    }

    public function setExternalVoltage(float $externalVoltage): self
    {
        $this->externalVoltage = $externalVoltage;
        return $this;
    }

    public function getIbutton(): ?string
    {
        return $this->ibutton;
    }

    public function setIbutton(string $ibutton): self
    {
        $this->ibutton = $ibutton;
        return $this;
    }

    public function getIsCalculated(): ?bool
    {
        return $this->isCalculated;
    }

    public function setIsCalculated(bool $isCalculated): self
    {
        $this->isCalculated = $isCalculated;
        return $this;
    }

    public function getIsOdometerCorrect(): ?bool
    {
        return $this->isOdometerCorrect;
    }

    public function setIsOdometerCorrect(bool $isOdometerCorrect): self
    {
        $this->isOdometerCorrect = $isOdometerCorrect;
        return $this;
    }

    public function getIsCalculatedIdling(): ?bool
    {
        return $this->isCalculatedIdling;
    }

    public function setIsCalculatedIdling(bool $isCalculatedIdling): self
    {
        $this->isCalculatedIdling = $isCalculatedIdling;
        return $this;
    }

    public function getIsCalculatedSpeeding(): ?bool
    {
        return $this->isCalculatedSpeeding;
    }

    public function setIsCalculatedSpeeding(bool $isCalculatedSpeeding): self
    {
        $this->isCalculatedSpeeding = $isCalculatedSpeeding;
        return $this;
    }

    public function getVehicleId(): ?int
    {
        return $this->vehicleId;
    }

    public function setVehicleId(int $vehicleId): self
    {
        $this->vehicleId = $vehicleId;
        return $this;
    }
}
