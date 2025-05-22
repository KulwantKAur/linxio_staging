<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\TLW212BL;

use App\Service\Tracker\Parser\Topflytech\Model\TLW1AndTLD1AE\AccidentViaAcc as AccidentViaAccTLW1;

class AccidentViaAcc extends AccidentViaAccTLW1
{
    /**
     * @inheritDoc
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }
}
