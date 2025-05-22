<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model;

use App\Service\Tracker\Parser\Topflytech\Traits\IgnitionTrait;
use App\Service\Tracker\Parser\Topflytech\Traits\MovementTrait;

abstract class BaseAccidentViaAcc
{
    use MovementTrait;
    use IgnitionTrait;

    public const PACKET_LENGTH_WITHOUT_DATA = 32;

    /**
     * @return mixed
     */
    abstract public function getGpsData();

    /**
     * @return mixed
     */
    abstract public function getLocationData();

    /**
     * @return mixed
     */
    abstract public function getDateTime();

    /**
     * @param string $textPayload
     * @return mixed
     */
    abstract public static function createFromTextPayload(string $textPayload);

    /**
     * @param string $textPayload
     * @return bool
     */
    abstract public static function hasPosition(string $textPayload): bool;

    /**
     * @param string $textPayload
     * @return string
     */
    abstract public static function getPositionPayload(string $textPayload): string;

    /**
     * @return DataAndGNSS|null
     */
    public function getDataAndGNSS(): ?DataAndGNSS
    {
        return $this->dataAndGNSS;
    }

    /**
     * @return bool
     */
    public function isHappened(): bool
    {
        return false;
    }
}
