<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Pivotel;

use App\Entity\Device;
use App\Service\Tracker\Interfaces\EncoderInterface;

class TcpEncoder implements EncoderInterface
{
    /**
     * @param bool $isAuthenticated
     * @param string|null $payload
     * @return string
     */
    public function encodeAuthentication(bool $isAuthenticated, ?string $payload = null): string
    {
    }

    /**
     * @inheritDoc
     */
    public function encodeData($data, ?Device $device = null)
    {
        return <<<RESPONSE
<?xml version = “1.0” encoding = “UTF-8” ?>
<PSG_MOmsg_rply>
    <MOID>2008</MOID>
    <status>success</status>
    <reason>null</reason>
</PSG_MOmsg_rply>
RESPONSE;
    }
}
