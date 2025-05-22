<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Teltonika\Model;

class SensorsDataElement
{
    public $numberOfIoBitElements;
    public $ioBitElements;
    public $bytes;
    public $data = [];

    /**
     * SensorsDataElement constructor.
     *
     * @param $ioBitElements
     * @param $numberOfIoBitElements
     * @param int $bytes
     */
    public function __construct(
        $ioBitElements,
        $numberOfIoBitElements,
        $bytes = 1
    ) {
        $this->ioBitElements = $ioBitElements;
        $this->numberOfIoBitElements = $numberOfIoBitElements;
        $this->bytes = $bytes;
        $this->parse($this);
    }

    /**
     * @param SensorsDataElement $sensorsDataElement
     */
    private function parse(SensorsDataElement $sensorsDataElement): void
    {
        for ($i = 0; $i < $sensorsDataElement->numberOfIoBitElements; $i++) {
            $elementsString = ($i * ($this->bytes * 2 + 2));
            $dataString = $elementsString + 2;
            $ioId = hexdec(substr($sensorsDataElement->ioBitElements, $elementsString, 2));
            $ioValue = hexdec(substr($sensorsDataElement->ioBitElements, $dataString, 2 * $this->bytes));
            $this->data[$ioId] = $ioValue;
        }
    }
}
