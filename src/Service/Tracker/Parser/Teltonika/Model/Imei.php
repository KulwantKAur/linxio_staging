<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Teltonika\Model;

use App\Service\Tracker\Interfaces\ImeiInterface;
use App\Service\Tracker\Parser\Teltonika\Exception\InvalidImeiException;
use JsonSerializable;

class Imei implements JsonSerializable, ImeiInterface
{
    const IMEI_LENGTH = 15;

    /**
     * @var string
     */
    private $imei;

    /**
     * @param string $imei
     *
     * @throws InvalidImeiException
     */
    public function __construct(string $imei)
    {
        if (strlen($imei) !== self::IMEI_LENGTH || !mb_check_encoding($imei, 'UTF-8')) {
            throw new InvalidImeiException("IMEI number is not valid.");
        }

        $this->imei = $imei;
    }

    /**
     * @return string
     */
    public function getImei(): string
    {
        return $this->imei;
    }

    public function jsonSerialize(): array
    {
        return [
            'imei' => $this->getImei()
        ];
    }

    public function __toString(): string
    {
        return $this->getImei();
    }

    public static function createFromHex(string $hexData): Imei
    {
        $imei = hex2bin($hexData);

        return new self($imei);
    }
}
