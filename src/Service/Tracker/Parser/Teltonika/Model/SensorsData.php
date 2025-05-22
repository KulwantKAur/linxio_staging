<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Teltonika\Model;

class SensorsData
{
    public $ioData = [];
    public $numberOfIoElements;

    /**
     * SensorsData constructor.
     * @param array $ioData
     * @param int $numberOfIoElements
     */
    public function __construct(array $ioData, int $numberOfIoElements)
    {
        $this->ioData = $ioData;
        $this->numberOfIoElements = $numberOfIoElements;
    }

    /**
     * @param string $payload
     * @param int $position
     *
     * @return SensorsData
     */
    public static function createFromHex(string $payload, &$position): SensorsData
    {
        // IO element ID of Event generated -- skip
        $position += 2;

        $numberOfIoElements = hexdec(substr($payload, $position, 2));
        $position += 2;

        $io1BitElements = self::parseIoData($payload, $position, 1);
        $io2BitElements = self::parseIoData($payload, $position, 2);
        $io4BitElements = self::parseIoData($payload, $position, 4);
        $io8BitElements = self::parseIoData($payload, $position, 8);

        $data = $io1BitElements + $io2BitElements + $io4BitElements + $io8BitElements;

        return new self($data, $numberOfIoElements);
    }

    /**
     * @param string $payload
     * @param $position
     * @param int $bytes
     * @return array
     */
    private static function parseIoData(string $payload, &$position, int $bytes): array
    {
        $numberOfIo1BitElements = substr($payload, $position, 2);
        $numberOfIo1BitElementsBin = hexdec($numberOfIo1BitElements);
        $position += 2;
        $numberOfIo1BitElementsLength = 2 * $numberOfIo1BitElementsBin + ($bytes * 2) * $numberOfIo1BitElementsBin;
        $ioBitElements = substr($payload, $position, $numberOfIo1BitElementsLength);
        $ioBitElementsModel = new SensorsDataElement($ioBitElements, $numberOfIo1BitElementsBin, $bytes);
        $position += $numberOfIo1BitElementsLength;

        return $ioBitElementsModel->data;
    }

    /**
     * @return array|null
     */
    public function getIOData(): ?array
    {
        return $this->ioData;
    }
}
