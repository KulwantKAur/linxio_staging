<?php

namespace App\Util;

class IpHelper
{
    public static function getIp(): ?string
    {
        if ($_SERVER['HTTP_CLIENT_IP'] ?? null) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } //if user is from the proxy
        elseif ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? null) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } //if user is from the remote address
        else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }
}