<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\TLW212BL;

use App\Service\Tracker\Parser\Topflytech\Model\TLW1AndTLD1AE\DriverBehaviorAM as DriverBehaviorAMTLW1AndTLD1AE;

class DriverBehaviorAM extends DriverBehaviorAMTLW1AndTLD1AE
{
    /**
     * @inheritDoc
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }
}
