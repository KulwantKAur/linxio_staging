<?php

namespace App\Service\Tracker;

use App\Service\BaseService;
use Doctrine\ORM\EntityManager;

abstract class SimulatorTrackerService extends BaseService
{
    /** @var EntityManager $em */
    public $em;
    public $logger;
    public $translator;

    /**
     * @param string $payload
     * @param string $modelName
     * @param \DateTimeInterface $createdAt
     * @return array
     */
    abstract public function getPayloadWithNewDateTime(
        string $payload,
        string $modelName,
        \DateTimeInterface $createdAt
    ): array;

    /**
     * @param string $imei
     * @return string|null
     */
    abstract public function getAuthPayload(
        string $imei
    ): string;

    /**
     * @param string $payload
     * @param int $deviceId
     * @param string $vendorName
     * @param string $modelName
     * @param $createdAt
     * @param string $imei
     * @param string|null $socketId
     * @return array
     */
    public function getDataWithUpdatedPayloadByDT(
        string $payload,
        int $deviceId,
        string $vendorName,
        string $modelName,
        $createdAt,
        ?string $imei,
        ?string $socketId = null
    ): array {
        $createdAtDT = BaseService::parseUrlDateToUTC($createdAt);
        $payloadOld = $payload;
        $payloadData = $this->getPayloadWithNewDateTime($payload, $modelName, $createdAtDT);
        $authPayload = $imei ? $this->getAuthPayload($imei) : null;

        return [
            'payload' => $payloadData['payload'],
            'payloadOld' => $payloadOld,
            'authPayload' => $authPayload,
            'deviceId' => $deviceId,
            'vendor' => $vendorName,
            'createdAt' => $payloadData['createdAt'],
            'createdAtOld' => $createdAtDT,
        ];
    }
}
