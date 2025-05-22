<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model;

use App\Service\Tracker\Parser\DataHelper;

/**
 * Class DataAndGNSS
 */
class DataAndGNSS
{
    public ?int $satellites;
    public $isHistoryData;
    public $isGps;
    public $isGpsInSleepMode;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->satellites = $data['satellites'] ?? null;
        $this->isHistoryData = $data['isHistoryData'] ?? null;
        $this->isGps = $data['isGps'] ?? null;
        $this->isGpsInSleepMode = $data['isGpsInSleepMode'] ?? null;
    }

    /**
     * @param string $textPayload
     * @return self
     */
    public static function createFromTextPayload(string $textPayload): self
    {
        $binaryStatusValues = DataHelper::getBinaryFromHex($textPayload);
        $values = DataHelper::addZerosToStartOfString($binaryStatusValues, 8);
        $satellites = bindec(substr($values, 3));
        $isHistoryData = substr($values, 0, 1);
        $isGps = substr($values, 1, 1);
        $isGpsInSleepMode = substr($values, 2, 1);

        return new self([
            'satellites' => intval($satellites),
            'isHistoryData' => boolval($isHistoryData),
            'isGps' => boolval($isGps),
            'isGpsInSleepMode' => boolval($isGpsInSleepMode),
        ]);
    }

    /**
     * @return bool|null
     */
    public function isGps(): ?bool
    {
        return $this->isGps;
    }

    /**
     * @return int|null
     */
    public function getSatellites()
    {
        return $this->satellites;
    }
}
