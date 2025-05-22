<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model;

use App\Service\Tracker\Parser\Topflytech\Data;

class BaseNetwork
{
    public const PACKET_MINIMAL_LENGTH = 100;

    public ?\DateTimeInterface $dateTime;
    public ?string $IMSI;
    public ?string $ICCID;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->dateTime = $data['dateTime'] ?? null;
        $this->IMSI = $data['IMSI'] ?? null;
        $this->ICCID = $data['ICCID'] ?? null;
    }

    /**
     * @param string $payload
     * @return string
     */
    private static function formatASCIIValue(string $payload): string
    {
        $payloadLength = strlen($payload);
        $newPayload = '';

        for ($letter = 0; $letter < $payloadLength; $letter++) {
            if ($letter % 2 != 0) {
                $newPayload .= $payload[$letter];
            }
        }

        return $newPayload;
    }

    /**
     * @param string $payload
     * @return self
     * @throws \Exception
     */
    public static function createFromTextPayload(string $payload): BaseNetwork
    {
        $operatorsLength = hexdec(substr($payload, 12, 2)) * 2;
        $operatorsName = substr($payload, 14, $operatorsLength);
        $accessTechNameLength = hexdec(substr($payload, 14 + $operatorsLength, 2)) * 2;
        $position = 14 + $operatorsLength;
        $accessTechName = substr($payload, $position + 2, $accessTechNameLength); // @todo convert to real text
        $position = $position + 2;
        $bandNameLength = hexdec(substr($payload, $position + $accessTechNameLength, 2)) * 2;
        $position = $position + $accessTechNameLength;
        $bandName = substr($payload, $position + 2, $bandNameLength); // @todo convert to real text
        $position = $position + 2;
        $IMSILength = hexdec(substr($payload, $position + $bandNameLength, 2)) * 2;
        $position = $position + $bandNameLength;
        $IMSI = self::formatASCIIValue(substr($payload, $position + 2, $IMSILength));
        $position = $position + 2;
        $ICCIDLength = hexdec(substr($payload, $position + $IMSILength, 2)) * 2;
        $position = $position + $IMSILength;
        $ICCID = self::formatASCIIValue(substr($payload, $position + 2, $ICCIDLength));

        return new self([
            'dateTime' => Data::formatDateTime(substr($payload, 0, 12)),
            'operatorsLength' => $operatorsLength,
            'operatorsName' => $operatorsName,
            'accessTechNameLength' => $accessTechNameLength,
            'accessTechName' => $accessTechName,
            'bandNameLength' => $bandNameLength,
            'bandName' => $bandName,
            'IMSILength' => $IMSILength,
            'IMSI' => $IMSI,
            'ICCIDLength' => $ICCIDLength,
            'ICCID' => $ICCID,
        ]);
    }

    /**
     * @return string|null
     */
    public function getIMSI(): ?string
    {
        return $this->IMSI;
    }

    /**
     * @param string|null $IMSI
     */
    public function setIMSI(?string $IMSI): void
    {
        $this->IMSI = $IMSI;
    }

    /**
     * @return string|null
     */
    public function getICCID(): ?string
    {
        return $this->ICCID;
    }

    /**
     * @param string|null $ICCID
     */
    public function setICCID(?string $ICCID): void
    {
        $this->ICCID = $ICCID;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getDateTime(): ?\DateTimeInterface
    {
        return $this->dateTime;
    }

    /**
     * @param \DateTimeInterface|null $dateTime
     */
    public function setDateTime(?\DateTimeInterface $dateTime): void
    {
        $this->dateTime = $dateTime;
    }
}
