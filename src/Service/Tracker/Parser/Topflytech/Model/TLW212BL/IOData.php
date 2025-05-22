<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\TLW212BL;

use App\Service\Tracker\Parser\DataHelper;
use App\Service\Tracker\Parser\Topflytech\Model\BaseIOData;

class IOData extends BaseIOData
{
    public $ignitionInput;
    public $externalPower;
    public $digitalInput1;
    public $digitalInput2;
    public $digitalInput3;
    public $digitalInput4;
    public $digitalInput5;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->externalPower = $data['externalPower'] ?? null;
        $this->ignitionInput = $data['ignitionInput'] ?? null;
        $this->digitalInput1 = $data['digitalInput1'] ?? null;
        $this->digitalInput2 = $data['digitalInput2'] ?? null;
        $this->digitalInput3 = $data['digitalInput3'] ?? null;
        $this->digitalInput4 = $data['digitalInput4'] ?? null;
        $this->digitalInput5 = $data['digitalInput5'] ?? null;
    }

    /**
     * @param string $textPayload
     * @return self
     */
    public static function createFromTextPayload(string $textPayload): self
    {
        $binaryValues = DataHelper::getBinaryFromHex($textPayload);
        $binaryValues = strrev(DataHelper::addZerosToStartOfString($binaryValues, 16));
        $set = str_split($binaryValues);

        return new self([
            'digitalInput5' => intval($set[9]),
            'digitalInput4' => intval($set[10]),
            'digitalInput3' => intval($set[11]),
            'digitalInput2' => intval($set[12]),
            'digitalInput1' => intval($set[13]),
            'ignitionInput' => intval($set[14]),
            'externalPower' => intval($set[15]),
        ]);
    }

    /**
     * @return int|null
     */
    public function getIgnitionInput(): ?int
    {
        return $this->ignitionInput;
    }

    /**
     * @return int|null
     */
    public function getExternalPower(): ?int
    {
        return $this->externalPower;
    }
}
