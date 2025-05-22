<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\TLD1DADE;

use App\Service\Tracker\Interfaces\DateTimePartPayloadInterface;
use App\Service\Tracker\Parser\Topflytech\Data;

/**
 * @example
 */
class DTCAndVIN implements DateTimePartPayloadInterface
{
    public const CONTENT_TYPE_DTC_CODE_1 = '4103';
    public const CONTENT_TYPE_DTC_CODE_2 = '410A';
    public const CONTENT_TYPE_DTC_CLEAN_RESPONSE_CODE = '4104';
    public const CONTENT_TYPE_VIN_CODE = '4105';

    public $dateTime;
    public $DTCData;
    public $VINData;
    public $contentType;
    public $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->dateTime = $data['dateTime'] ?? null;
        $this->DTCData = $data['DTCData'] ?? null;
        $this->VINData = $data['VINData'] ?? null;
    }

    /**
     * @param string $payload
     * @return self
     * @throws \Exception
     */
    public static function createFromTextPayload(string $payload): self
    {
        $contentType = substr($payload, 20, 4);

        switch ($contentType) {
            case self::CONTENT_TYPE_VIN_CODE:
                $VINData = VIN::createFromTextPayload(substr($payload, 12));
                break;
            default:
                $DTCData = DTC::createFromTextPayload(substr($payload, 12));
                break;
        }

        return new self([
            'dateTime' => Data::formatDateTime(substr($payload, 0, 12)),
            'DTCData' => $DTCData ?? null,
            'VINData' => $VINData ?? null
        ]);
    }

    /**
     * @return \DateTime|null
     */
    public function getDateTime(): ?\DateTime
    {
        return $this->dateTime;
    }

    /**
     * @param \DateTime|null $dateTime
     */
    public function setDateTime(?\DateTime $dateTime): void
    {
        $this->dateTime = $dateTime;
    }

    /**
     * @inheritDoc
     */
    public function getDateTimePayload(string $payload): string
    {
        return substr($payload, Data::DATA_START_PACKET_POSITION + 0, 12);
    }

    /**
     * @inheritDoc
     */
    public function getPayloadWithNewDateTime(string $payload, string $dtString): string
    {
        return substr_replace($payload, $dtString, Data::DATA_START_PACKET_POSITION + 0, 12);
    }

    /**
     * @return DTC|null
     */
    public function getDTCData(): ?DTC
    {
        return $this->DTCData;
    }

    /**
     * @return VIN|null
     */
    public function getVINData(): ?VIN
    {
        return $this->VINData;
    }

    /**
     * @return DTC|VIN|null
     */
    public function getData()
    {
        return $this->getDTCData() ? $this->getDTCData() : $this->getVINData();
    }

    /**
     * @return array|null
     */
    public function getDataArray()
    {
        return $this->getData() ? $this->getData()->toArray() : null;
    }

    /**
     * @return array
     */
    public function getCodes(): array
    {
        switch (true) {
            case $this->getDTCData() && $this->getDTCData()->getDTCItems():
                $codes = array_column($this->getDTCData()->getDTCItems(), 'code');
                break;
            case $this->getVINData() && $this->getVINData()->getCode():
                $codes = $this->getVINData()->getVINItems();
                break;
            default:
                $codes = [];
                break;
        }

        return $codes;
    }
}
