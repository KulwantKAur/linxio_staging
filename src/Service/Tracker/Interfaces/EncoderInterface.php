<?php

namespace App\Service\Tracker\Interfaces;

use App\Entity\Device;

interface EncoderInterface
{
    /**
     * @param bool $isAuthenticated
     * @param string|null $data
     * @return string
     */
    public function encodeAuthentication(bool $isAuthenticated, ?string $data = null): string;

    /**
     * @param $data
     * @param Device|null $device
     * @return string
     */
    public function encodeData($data, ?Device $device = null);
}
