<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\TLP1;

use App\Service\Tracker\Parser\DataHelper;

class PositionStatus
{
    public $ignition;
    public $signalPower;
    public $solarChargingStatus;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->ignition = $data['ignition'] ?? null;
        $this->signalPower = $data['signalPower'] ?? null;
        $this->solarChargingStatus = $data['solarChargingStatus'] ?? null;
    }

    /**
     * @param string $textPayload
     * @return self
     * @throws \Exception
     */
    public static function createFromTextPayload(string $textPayload): self
    {
        $binaryValues = DataHelper::getBinaryFromHex($textPayload);
        $binaryValues = strrev(DataHelper::addZerosToStartOfString($binaryValues, 16));
        $set = str_split($binaryValues);

        return new self([
            'ignition' => intval($set[2]),
            'solarChargingStatus' => (intval($set[3]) == 1),
            'signalPower' => intval(bindec(substr(strrev($binaryValues), 5, 7))),
        ]);
    }

    /**
     * @return int|null
     */
    public function getIgnition(): ?int
    {
        return $this->ignition;
    }

    /**
     * @return mixed|null
     */
    public function getSignalPower(): ?int
    {
        return $this->signalPower;
    }

    /**
     * @return bool|null
     */
    public function getSolarChargingStatus(): ?bool
    {
        return $this->solarChargingStatus;
    }
}
