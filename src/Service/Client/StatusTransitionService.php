<?php

namespace App\Service\Client;

use App\Entity\Client;

/**
 * Class StatusTransitionService
 * @package App\Service\Client
 */
class StatusTransitionService
{
    protected const ANY_STATUS = 'ANY_STATUS';
    protected const NONE_STATUS = 'NONE_STATUS';

    public function __construct()
    {
    }

    /**
     *  Mapping for transition
     *  @example ['from1' => ['to1', 'to2'], 'from2' => 'to any']
     */
    protected const TO_STATUS_TRANSITION_MAPPING = [
        Client::STATUS_CLIENT => self::ANY_STATUS,
        Client::STATUS_POTENTIAL => self::ANY_STATUS,
        Client::STATUS_CLOSED => self::ANY_STATUS,
        Client::STATUS_DELETED => self::ANY_STATUS,
        Client::STATUS_PARTIALLY_BLOCKED_BILLING => self::ANY_STATUS,
        Client::STATUS_BLOCKED_BILLING => self::ANY_STATUS,
        Client::STATUS_BLOCKED => [
            Client::STATUS_CLIENT,
            Client::STATUS_DELETED,
        ],
        Client::STATUS_BLOCKED_OVERDUE => [
            Client::STATUS_CLIENT,
            Client::STATUS_DEMO,
        ],
        Client::STATUS_DEMO => self::ANY_STATUS,
    ];

    /**
     *  Mapping for transition
     *  @example ['to1' => ['from1', 'from2'], 'to2' => 'from any']
     */
    protected const FROM_STATUS_TRANSITION_MAPPING = [
        Client::STATUS_CLIENT => self::ANY_STATUS,
        Client::STATUS_POTENTIAL => self::ANY_STATUS,
        Client::STATUS_CLOSED => self::ANY_STATUS,
        Client::STATUS_DELETED => self::ANY_STATUS,
        Client::STATUS_PARTIALLY_BLOCKED_BILLING => self::ANY_STATUS,
        Client::STATUS_BLOCKED_BILLING => self::ANY_STATUS,
        Client::STATUS_BLOCKED => [
            Client::STATUS_CLIENT,
            Client::STATUS_DEMO,
            Client::STATUS_DELETED,
            Client::STATUS_PARTIALLY_BLOCKED_BILLING,
            Client::STATUS_BLOCKED_BILLING,
            Client::STATUS_BLOCKED_OVERDUE,
        ],
        Client::STATUS_BLOCKED_OVERDUE => [
            Client::STATUS_CLIENT,
            Client::STATUS_DEMO,
            Client::STATUS_DELETED,
            Client::STATUS_PARTIALLY_BLOCKED_BILLING,
            Client::STATUS_BLOCKED_BILLING,
            Client::STATUS_BLOCKED_OVERDUE,
        ],
        Client::STATUS_DEMO => [
            Client::STATUS_POTENTIAL,
            Client::STATUS_CLOSED,
            Client::STATUS_BLOCKED,
            Client::STATUS_DELETED,
            Client::STATUS_PARTIALLY_BLOCKED_BILLING,
            Client::STATUS_BLOCKED_BILLING,
            Client::STATUS_BLOCKED_OVERDUE,
        ],
    ];

    /**
     * @param $fromStatus
     * @param $toStatus
     * @return bool
     */
    public function isAvailable($fromStatus, $toStatus): bool
    {
        return $fromStatus === $toStatus
            || ($this->isAvailableFrom($fromStatus, $toStatus) && $this->isAvailableTo($fromStatus, $toStatus));
    }

    /**
     * @param $fromStatus
     * @param $toStatus
     * @return bool
     */
    protected function isAvailableFrom($fromStatus, $toStatus): bool
    {
        $availableStatuses = self::TO_STATUS_TRANSITION_MAPPING[$fromStatus];

        return self::ANY_STATUS === $availableStatuses || in_array($toStatus, $availableStatuses, true);
    }

    /**
     * @param $fromStatus
     * @param $toStatus
     * @return bool
     */
    protected function isAvailableTo($fromStatus, $toStatus): bool
    {
        $availableStatuses = self::FROM_STATUS_TRANSITION_MAPPING[$toStatus];

        return self::ANY_STATUS === $availableStatuses || in_array($fromStatus, $availableStatuses, true);
    }

}