<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Ulbotech\Model;

use App\Service\Tracker\Parser\DataHelper;
use App\Service\Tracker\Parser\Ulbotech\Data;

/**
 * Class AnalogData
 * @package App\Service\Tracker\Parser\Ulbotech\Model
 *
 * @example ADC:0;15.16;1;28.77;2;3.55 | ADC:0;12.56;1;32.89;2;4.22;3;5
 */
class AnalogData
{
    public const EXTERNAL_POWER_VOLTAGE = 0;
    public const DEVICE_TEMPERATURE = 1;
    public const DEVICE_BACKUP_BATTERY_VOLTAGE = 2;
    public const ANALOG_INPUT_VOLTAGE = 3;

    public $externalPowerVoltage; // V
    public $deviceTemperature; // T
    public $deviceBackupBatteryVoltage; // V
    public $analogInputVoltage; // V

    /**
     * AnalogData constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->externalPowerVoltage = $data['externalPowerVoltage'] ?? null;
        $this->deviceTemperature = $data['deviceTemperature'] ?? null;
        $this->deviceBackupBatteryVoltage = $data['deviceBackupBatteryVoltage'] ?? null;
        $this->analogInputVoltage = $data['analogInputVoltage'] ?? null;
    }

    /**
     * @param $textPayload
     * @return self
     */
    public static function createFromTextPayload(string $textPayload): self
    {
        $set = explode(Data::DATA_PART_SEPARATOR, substr($textPayload, 4));
        $formattedData = [];

        foreach ($set as $key => $item) {
            if ($key % 2 == 0) {
                $formattedData[$set[$key]] = $set[$key + 1];
            }
        }

        return new self([
            'externalPowerVoltage' => Data::getFloatValueFromSetByKey($formattedData, self::EXTERNAL_POWER_VOLTAGE),
            'deviceTemperature' => Data::getFloatValueFromSetByKey($formattedData, self::DEVICE_TEMPERATURE),
            'deviceBackupBatteryVoltage' => Data::getFloatValueFromSetByKey(
                $formattedData,
                self::DEVICE_BACKUP_BATTERY_VOLTAGE
            ),
            'analogInputVoltage' => Data::getFloatValueFromSetByKey($formattedData, self::ANALOG_INPUT_VOLTAGE),
        ]);
    }

    /**
     * @return float|null
     */
    public function getExternalPowerVoltage(): ?float
    {
        return DataHelper::increaseValueToMilli($this->externalPowerVoltage);
    }

    /**
     * @param float|null $externalPowerVoltage
     */
    public function setExternalPowerVoltage(?float $externalPowerVoltage): void
    {
        $this->externalPowerVoltage = $externalPowerVoltage;
    }

    /**
     * @return float|null
     */
    public function getDeviceTemperature(): ?float
    {
        return DataHelper::increaseValueToMilli($this->deviceTemperature);
    }

    /**
     * @param float|null $deviceTemperature
     */
    public function setDeviceTemperature(?float $deviceTemperature): void
    {
        $this->deviceTemperature = $deviceTemperature;
    }

    /**
     * @return float|null
     */
    public function getDeviceBackupBatteryVoltage(): ?float
    {
        return DataHelper::increaseValueToMilli($this->deviceBackupBatteryVoltage);
    }

    /**
     * @param float|null $deviceBackupBatteryVoltage
     */
    public function setDeviceBackupBatteryVoltage(?float $deviceBackupBatteryVoltage): void
    {
        $this->deviceBackupBatteryVoltage = $deviceBackupBatteryVoltage;
    }
}
