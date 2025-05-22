<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\TLP1\BLE;

use App\Service\Tracker\Parser\Topflytech\Model\BLE\DriverID as DriverIDBase;

/**
 * @example
 */
class DriverID extends DriverIDBase
{
    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }
}
