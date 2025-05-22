<?php

namespace App\Service\Streamax\Model;

/**
 * @example { "code": 200, "data": { "ERRORCAUSE": "SUCCESS", "S": { "SU": 0, "STC": 3, "SW": 1, "ALARM": 0, "G3S": 4, "G4S": 0, "HTR": 0, "H": 0, "G3": 3, "G4": 2, "TC": 0, "TD": 6900, "SINFO": [ { "S": 0, "T": 2, "LS": 17045651456, "DS": 6, "O": 0, "TS": 255684771840 }, { "S": 0, "T": 2, "LS": 249242320896, "DS": 5, "O": 0, "TS": 255684771840 }, { "S": 1, "T": 1, "LS": 0, "DS": 0, "O": 0, "TS": 0 } ], "RE": [ 2, 2, 2, 2 ], "S": 8200, "BV": 0, "T": "20240524150933", "V": 2760, "W": 1, "TM": "5749.940000", "WS": 5, "VS": [ 0, 0, 0, 0 ] }, "ERRORCODE": 0 }, "message": "Success", "success": true }
 */
class StreamaxDeviceInfo extends StreamaxModel
{
    public ?float $voltage;

    public function __construct(array $fields)
    {
        $parentField = $fields['S'] ?? null;

        if (!$parentField) {
            return;
        }

        $this->voltage = $parentField['V'] ?? null;
    }

    public function getVoltage(): ?float
    {
        return $this->voltage;
    }

    public function getVoltageMilli(): ?float
    {
        return $this->getVoltage() ? $this->getVoltage() * 10 : null;
    }

    public function setVoltage(?float $voltage): void
    {
        $this->voltage = $voltage;
    }
}

