<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\TLW212BL;

use App\Service\Tracker\Parser\DataHelper;
use App\Service\Tracker\Parser\Topflytech\Model\BaseOutputData;

class OutputData extends BaseOutputData
{
    public $Vout;
    public $VoutValue;
    public $digitalOutput1;
    public $digitalOutput2;
    public $digitalOutput3;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->Vout = $data['Vout'] ?? null;
        $this->VoutValue = $data['VoutValue'] ?? null;
        $this->digitalOutput1 = $data['digitalOutput1'] ?? null;
        $this->digitalOutput2 = $data['digitalOutput2'] ?? null;
        $this->digitalOutput3 = $data['digitalOutput3'] ?? null;
    }

    /**
     * @param string $textPayload
     * @return self
     */
    public static function createFromTextPayload(string $textPayload): self
    {
        $binaryValues = DataHelper::getBinaryFromHex($textPayload);
        $binaryValues = strrev(DataHelper::addZerosToStartOfString($binaryValues, 8));
        $set = str_split($binaryValues);

        return new self([
            'digitalOutput3' => intval($set[3]),
            'digitalOutput2' => intval($set[4]),
            'digitalOutput1' => intval($set[5]),
            'Vout' => intval($set[6]),
            'VoutValue' => intval($set[7]) == 1 ? 12 : 5,
        ]);
    }
}
