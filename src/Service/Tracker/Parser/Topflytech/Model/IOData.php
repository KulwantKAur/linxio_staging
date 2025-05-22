<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model;

use App\Service\Tracker\Parser\DataHelper;

/**
 * Class IOData
 * @package App\Service\Tracker\Parser\Topflytech\Model
 */
class IOData extends BaseIOData
{
    public $ignitionInput;
    public $externalPower;
    public $ACDigitalInput;
    public $digitalInput3;
    public $digitalInput4;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->ignitionInput = $data['ignitionInput'] ?? null;
        $this->externalPower = $data['externalPower'] ?? null;
        $this->ACDigitalInput = $data['ACDigitalInput'] ?? null;
        $this->digitalInput3 = $data['digitalInput3'] ?? null;
        $this->digitalInput4 = $data['digitalInput4'] ?? null;
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
            'digitalInput4' => intval($set[11]),
            'digitalInput3' => intval($set[12]),
            'ACDigitalInput' => intval($set[13]),
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
