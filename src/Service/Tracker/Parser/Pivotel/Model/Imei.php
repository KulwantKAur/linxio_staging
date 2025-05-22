<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Pivotel\Model;

use App\Service\Tracker\Interfaces\ImeiInterface;

class Imei implements ImeiInterface
{
    /**
     * @var string
     */
    private $imei;

    /**
     * @param string $imei
     */
    public function __construct(string $imei)
    {
        $this->imei = $imei;
    }

    /**
     * @inheritDoc
     */
    public function getImei(): string
    {
        return $this->imei;
    }

    public function __toString(): string
    {
        return $this->getImei();
    }
}
