<?php

namespace App\Entity\Tracker;

use App\Entity\DeviceModel;
use App\Entity\DeviceVendor;
use App\Entity\Team;

trait TrackerHistoryTrait
{
    /**
     * @return array|null
     */
    public function getExtraData(): ?array
    {
        return $this->extraData;
    }

    /**
     * @param array|null $extraData
     */
    public function setExtraData(?array $extraData): void
    {
        $this->extraData = $extraData;
    }

    /**
     * @param array|null $extraData
     */
    public function setOBDExtraData(?array $extraData): void
    {
        if ($extraData) {
            $this->extraData[TrackerHistory::OBD_EXTRA_DATA_NAME] = $extraData;
        }
    }

    /**
     * @return array|null
     */
    public function getOBDExtraData(): ?array
    {
        return $this->extraData[TrackerHistory::OBD_EXTRA_DATA_NAME] ?? null;
    }

    /**
     * @param array|null $extraData
     */
    public function setBLEExtraData(?array $extraData): void
    {
        if ($extraData) {
            $this->extraData[TrackerHistory::BLE_EXTRA_DATA_NAME] = $extraData;
        }
    }

    /**
     * @param array|null $extraData
     */
    public function setBLEDriverSensorExtraData(?array $extraData): void
    {
        if ($extraData) {
            $this->extraData[TrackerHistory::BLE_EXTRA_DATA_NAME]
                [TrackerHistory::BLE_DRIVER_SENSOR_EXTRA_DATA_NAME] = $extraData;
        }
    }

    /**
     * @param array|null $extraData
     */
    public function setBLETempAndHumidityExtraData(?array $extraData): void
    {
        if ($extraData) {
            $this->extraData[TrackerHistory::BLE_EXTRA_DATA_NAME]
                [TrackerHistory::BLE_TEMP_AND_HUMIDITY_EXTRA_DATA_NAME] = $extraData;
        }
    }

    /**
     * @return array|null
     */
    public function getBLETempAndHumidityExtraData(): ?array
    {
        return $this->extraData[TrackerHistory::BLE_EXTRA_DATA_NAME]
            [TrackerHistory::BLE_TEMP_AND_HUMIDITY_EXTRA_DATA_NAME] ?? null;
    }

    /**
     * @param array|null $extraData
     */
    public function setBLESOSExtraData(?array $extraData): void
    {
        if ($extraData) {
            $this->extraData[TrackerHistory::BLE_EXTRA_DATA_NAME][TrackerHistory::BLE_SOS_DATA_NAME] = $extraData;
        }
    }

    /**
     * @param array|null $extraData
     */
    public function setIOExtraData(?array $extraData): void
    {
        if ($extraData) {
            $this->extraData[TrackerHistory::IO_EXTRA_DATA_NAME] = $extraData;
        }
    }

    /**
     * @return array|null
     */
    public function getIOExtraData(): ?array
    {
        return $this->extraData[TrackerHistory::IO_EXTRA_DATA_NAME] ?? null;
    }

    /**
     * @param string|null $deviceVendorName
     * @param array $data
     * @return array
     */
    public function addExtraFieldsByDeviceVendor(?string $deviceVendorName, array $data): array
    {
        switch ($deviceVendorName) {
            case DeviceVendor::VENDOR_TOPFLYTECH:
            case DeviceVendor::VENDOR_PIVOTEL:
                $data['batteryVoltagePercentage'] = $this->getBatteryVoltagePercentage();
                break;
            default:
                break;
        }

        return $data;
    }

    /**
     * @param string|null $deviceModelName
     * @param array $data
     * @return array
     */
    public function addExtraFieldsByDeviceModel(?string $deviceModelName, array $data): array
    {
        switch ($deviceModelName) {
            case DeviceModel::TOPFLYTECH_TLP1_SF:
            case DeviceModel::TOPFLYTECH_TLP1_LF:
                $data['solarChargingStatus'] = $this->getSolarChargingStatus();
                break;
            case DeviceModel::TOPFLYTECH_TLD1_DA_DE:
            case DeviceModel::TOPFLYTECH_TLD2_DA_DE:
                $data[TrackerHistory::OBD_EXTRA_DATA_NAME] = $this->getOBDExtraData();
                break;
            default:
                break;
        }

        return $data;
    }

    /**
     * @param array|null $extraData
     */
    public function setAlarmExtraData(?array $extraData): void
    {
        if ($extraData) {
            $this->extraData[TrackerHistory::ALARM_EXTRA_DATA_NAME] = $extraData;
        }
    }

    /**
     * @param bool|null $isSOSButton
     */
    public function setIsSOSButton(?bool $isSOSButton): void
    {
        if ($isSOSButton) {
            $this->extraData[TrackerHistory::BLE_SOS_DATA_NAME]['isSOSButton'] = $isSOSButton;
        }
    }

    /**
     * @return int|null
     */
    public function getTraccarPositionId(): ?int
    {
        return $this->traccarPositionId;
    }

    /**
     * @param int|null $traccarPositionId
     * @return TrackerHistory
     */
    public function setTraccarPositionId(?int $traccarPositionId): self
    {
        $this->traccarPositionId = $traccarPositionId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getSatellites(): ?int
    {
        return $this->satellites;
    }

    /**
     * @param int|null $satellites
     * @return TrackerHistory
     */
    public function setSatellites(?int $satellites): self
    {
        $this->satellites = $satellites;

        return $this;
    }

    /**
     * @return Team|null
     */
    public function getTeam(): ?Team
    {
        return $this->team;
    }

    /**
     * @param Team|null $team
     */
    public function setTeam(?Team $team): void
    {
        $this->team = $team;
    }

    /**
     * @param int|null $jammerStatus
     */
    public function setIsJammerAlarm(?int $jammerStatus): void
    {
        if (!is_null($jammerStatus)) {
            $this->extraData[TrackerHistory::ALARM_EXTRA_DATA_NAME]['isJammerAlarm'] = $jammerStatus;
        }
    }

    /**
     * @param int|null $accidentStatus
     */
    public function setAccidentHappened(?int $accidentStatus): void
    {
        if (!is_null($accidentStatus)) {
            $this->extraData[TrackerHistory::ACCIDENT_EXTRA_DATA_NAME]['accidentHappened'] = $accidentStatus;
        }
    }
}
