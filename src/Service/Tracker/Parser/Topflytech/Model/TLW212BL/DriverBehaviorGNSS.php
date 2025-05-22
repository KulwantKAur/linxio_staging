<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\TLW212BL;

use App\Service\Tracker\Parser\Topflytech\Model\TLW1AndTLD1AE\DriverBehaviorGNSS as DriverBehaviorGNSSAMTLW1AndTLD1AE;

class DriverBehaviorGNSS extends DriverBehaviorGNSSAMTLW1AndTLD1AE
{
    /**
     * @inheritDoc
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }
}