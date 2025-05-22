<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\TLW212BL\BLE;

use App\Service\Tracker\Parser\Topflytech\Model\BLE\SOS as SOSBase;

class SOS extends SOSBase
{
    /**
     * @inheritDoc
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }
}
