<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Ulbotech;

use App\Entity\Device;
use App\Service\Tracker\Interfaces\DecoderInterface;
use App\Service\Tracker\Interfaces\ImeiInterface;
use App\Service\Tracker\Parser\Ulbotech\Model\HeartBeat;

class TcpDecoder implements DecoderInterface
{
    private const DATA_LENGTH_WITH_IMEI = 22;
    private const DATA_START_SYMBOL = '*';
    private const DATA_END_SYMBOL = '#';

    /**
     * @param string $data
     * @return bool
     */
    public function isAuthentication(string $data): bool
    {
        return strlen($data) == self::DATA_LENGTH_WITH_IMEI;
    }

    /**
     * @param string $data
     * @return bool
     */
    public function isCorrectTextPayload(string $data): bool
    {
        return (substr($data, 0, 1) == self::DATA_START_SYMBOL)
            && (substr($data, strlen($data) - 1, 1) == self::DATA_END_SYMBOL);
    }

    /**
     * @param string $payload
     * @return HeartBeat
     * @throws \Exception
     */
    public function decodeAuthentication(string $payload): ImeiInterface
    {
        return HeartBeat::createFromTextPayload($payload);
    }

    /**
     * @param string|null $payload
     * @return string|null
     * @throws \Exception
     */
    public function decodePayload(?string $payload): ?string
    {
        if (!$payload) {
            return null;
        }

        try {
            $convertedPayload = hex2bin($payload);

            if (!mb_check_encoding($convertedPayload, 'UTF-8')) {
                throw new \Exception("Payload is not valid UTF-8.");
            }

            return $convertedPayload;
        } catch (\Exception $exception) {
            throw new \Exception('Unable to convert payload: ' . $exception->getMessage());
        }
    }

    /**
     * @param string $textPayload
     * @param Device|null $device
     * @return array
     * @throws \Exception
     */
    public function decodeData(string $textPayload, ?Device $device = null): array
    {
        if (!$this->isCorrectTextPayload($textPayload)) {
            throw new \Exception('Data is not correct');
        }

        $data = [];
        $textPayloadParts = explode(self::DATA_END_SYMBOL, $textPayload);
        unset($textPayloadParts[count($textPayloadParts) - 1]);

        foreach ($textPayloadParts as $key => $textPayload) {
            $dataModel = (new Data())->createFromTextPayload($textPayload);

            if ($dataModel) {
                $data[] = $dataModel;
            }
        }

        return $this->removeDuplicatesWithTime($data);
    }

    /**
     * @param array $data
     * @return array
     * @throws \Exception
     * @todo refactor it when trip data with the same timestamp will be investigated
     */
    private function removeDuplicatesWithTime(array $data): array
    {
        /** @var Data $datum */
        foreach ($data as $key => $datum) {
            $prevDatum = $data[$key - 1] ?? null;

            if ($prevDatum && ($datum->getDateTime() == $prevDatum->getDateTime())) {
                if ($datum->getTripData()) {
                    unset($data[$key]);
                }

                if ($prevDatum->getTripData()) {
                    unset($data[$key - 1]);
                }
            }
        }


        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    public function orderByDateTime(array $data): array
    {
        usort($data, function ($a, $b) {
            if ($a == $b) {
                return 0;
            }

            return ($a->getDateTime() < $b->getDateTime()) ? -1 : 1;
        });

        return $data;
    }

    /**
     * @param string $payload
     * @param string $modelName
     * @param \DateTimeInterface $createdAt
     * @return array
     * @throws \Exception
     */
    public function encodePayloadWithNewDateTime(
        string $payload,
        string $modelName,
        \DateTimeInterface $createdAt
    ): array {
        $payload = $this->decodePayload($payload);

        if (!$payload || !$this->isCorrectTextPayload($payload)) {
            return [
                'payload' => null,
                'createdAt' => null,
            ];
        }

        $payloads = [];
        $payloadParts = explode(self::DATA_END_SYMBOL, $payload);
        unset($payloadParts[count($payloadParts) - 1]);

        foreach ($payloadParts as $key => $payload) {
            $dtPayload = Data::getDateTimePayload($payload);
            $dt = Data::formatDateTime($dtPayload);

            if (!$dt) {
                continue;
            }

            $dtNeeded = (new \DateTime())->getTimestamp() - ($createdAt->getTimestamp() - $dt->getTimestamp());
            $dt->setTimestamp($dtNeeded);
            $dtString = Data::encodeDateTime($dt);
            $payload = Data::getPayloadWithNewDateTime($payload, $dtString);
            $payloads[] = $payload;
        }

        $payload = TcpEncoder::encodePayload(implode(self::DATA_END_SYMBOL, $payloads) . self::DATA_END_SYMBOL);

        return [
            'payload' => $payload,
            'createdAt' => $dt ?? null,
        ];
    }
}
