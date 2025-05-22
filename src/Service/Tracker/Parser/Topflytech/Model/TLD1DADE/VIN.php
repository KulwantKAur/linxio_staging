<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\TLD1DADE;

use App\Service\Tracker\Parser\Topflytech\Data;

/**
 * @example 262609002f0107086528404073135220120904281755aa501441055741555a5a5a344d384a44303138343238510d0a
 */
class VIN
{
    public $header;
    public $serialNumber;
    public $packetLength;
    public $contentType;
    public $code;

    /**
     * @param string $hex
     * @return string|null
     */
    private static function hexToASCII(string $hex): ?string
    {
        if (Data::isValueNullableFF($hex)) {
            return null;
        }

        $hexLength = strlen($hex);

        for ($i = 0, $str = ''; $i < $hexLength; $i += 2) {
            $str .= chr(hexdec(substr($hex, $i, 2)));
        }

        return mb_check_encoding($str, 'UTF-8') ? $str : null;
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
        $this->code = $data['code'] ?? null;
    }

    /**
     * @param string $textPayload
     * @return self
     * @throws \Exception
     */
    public static function createFromTextPayload(string $textPayload): self
    {
        return new self([
            'header' => substr($textPayload, 0, 4),
            'serialNumber' => substr($textPayload, 4, 2),
            'packetLength' => substr($textPayload, 6, 2),
            'contentType' => substr($textPayload, 8, 4),
            'code' => self::hexToASCII(substr($textPayload, 12, 34)),
        ]);
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @return array
     */
    public function getVINItems(): array
    {
        return $this->getCode() ? ['code' => $this->getCode()] : [];
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'VINItems' => [$this->getVINItems()],
        ];
    }
}
