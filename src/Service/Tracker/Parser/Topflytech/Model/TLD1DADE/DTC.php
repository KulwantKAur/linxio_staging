<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\TLD1DADE;

use App\Exceptions\ValidationException;

/**
 * @example 262609001f0c8d086447504965853621081604103255aa1004410a005f0d0a
 * @example 26260900220c8d086447504965853621081604103255aa1007410a005201015f0d0a
 * @example 26260900250c8d086528404264535221081604103255aa100a410a005201018301025f0d0a
 * @link https://www.engine-codes.com/
 */
class DTC
{
    private const DTC_DEFAULT_PACKET_LENGTH = 4;
    private const DTC_DATA_LENGTH = 3;
    private const STATUS_CONFIRMED_CODE = '01';
    private const STATUS_PROBABLY_CODE = '02';
    private const SAE_STANDARDS_LIST = [
        '00' => 'J1979',
        '01' => 'J1939',
    ];
    private const DTC_CODES_LIST = [
        '0' => 'P0',
        '1' => 'P1',
        '2' => 'P2',
        '3' => 'P3',
        '4' => 'C0',
        '5' => 'C1',
        '6' => 'C2',
        '7' => 'C3',
        '8' => 'B0',
        '9' => 'B1',
        'a' => 'B2',
        'b' => 'B3',
        'c' => 'U0',
        'd' => 'U1',
        'e' => 'U2',
        'f' => 'U3',
    ];

    public $header;
    public $serialNumber;
    public $DTCCount;
    public $DTCItems;
    public $SAE;
    public $packetLength;
    public $contentType;

    /**
     * @param string $payload
     * @return string
     * @throws \Exception
     */
    private static function parseDTCCode(string $payload): string
    {
        $firstByte = substr($payload, 0, 1);

        if (!array_key_exists($firstByte, self::DTC_CODES_LIST)) {
            throw new \Exception('Unsupported device DTC code: ' . $firstByte);
        }

        return self::DTC_CODES_LIST[$firstByte] . substr($payload, 1, 3);
    }

    /**
     * @param int|null $DTCCount
     * @param string $payload
     * @return array|null
     * @throws \Exception
     */
    private static function parseDTC(?int $DTCCount, string $payload): ?array
    {
        if ($DTCCount && $DTCCount > 0) {
            $DTCItems = [];
            $DTCItemLength = self::DTC_DATA_LENGTH * 2;

            for ($i = 0; $i < $DTCCount; $i++) {
                $DTCItemLengthIteration = $DTCItemLength * $i;
                $DTCItem = [
                    'code' => self::parseDTCCode(substr($payload, $DTCItemLengthIteration, 4)),
                    'status' => substr($payload, $DTCItemLengthIteration + 4, 2),
                ];
                $DTCItem['statusText'] = self::getDTCStatusText($DTCItem['status']);
                $DTCItems[] = $DTCItem;
            }

            return $DTCItems;
        }

        return null;
    }

    /**
     * @param string $status
     * @return string
     */
    private static function getDTCStatusText(string $status): string
    {
        return ($status == self::STATUS_CONFIRMED_CODE) ? 'confirmed' : 'probably';
    }

    /**
     * @param string $status
     * @return string|null
     */
    private static function getSAEStandard(string $status): ?string
    {
        $isExists = array_key_exists($status, self::SAE_STANDARDS_LIST);

        return $isExists ? self::SAE_STANDARDS_LIST[$status] : null;
    }

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->header = $data['header'] ?? null;
        $this->serialNumber = $data['serialNumber'] ?? null;
        $this->packetLength = $data['packetLength'] ?? null;
        $this->contentType = $data['contentType'] ?? null;
        $this->DTCCount = $data['DTCCount'] ?? null;
        $this->DTCItems = $data['DTCItems'] ?? null;
        $this->SAE = $data['SAE'] ?? null;
    }

    /**
     * @param string $textPayload
     * @return self
     * @throws \Exception
     */
    public static function createFromTextPayload(string $textPayload): self
    {
        $packetLength = hexdec(substr($textPayload, 6, 2));
        $DTCCount = $packetLength
            ? ($packetLength - self::DTC_DEFAULT_PACKET_LENGTH) / self::DTC_DATA_LENGTH
            : null;

        if (is_int($DTCCount)) {
            $DTCItems = self::parseDTC($DTCCount, substr($textPayload, 14));
        } else {
            $DTCItems = [];
            $DTCCount = 0;
        }

        return new self([
            'header' => substr($textPayload, 0, 4),
            'serialNumber' => substr($textPayload, 4, 2),
            'packetLength' => $packetLength,
            'contentType' => substr($textPayload, 8, 4),
            'DTCCount' => $DTCCount,
            'DTCItems' => $DTCItems,
            'SAE' => self::getSAEStandard(substr($textPayload, 12, 2)),
        ]);
    }

    /**
     * @return array|null
     */
    public function getDTCItems(): ?array
    {
        return $this->DTCItems;
    }

    /**
     * @return string|null
     */
    public function getSAE(): ?string
    {
        return $this->SAE;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'DTCItems' => $this->getDTCItems(),
            'SAE' => $this->getSAE(),
        ];
    }
}
