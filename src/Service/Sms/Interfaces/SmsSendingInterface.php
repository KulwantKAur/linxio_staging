<?php

namespace App\Service\Sms\Interfaces;

interface SmsSendingInterface
{
    /**
     * @param $to
     * @param string $text
     * @param $is2FA
     * @param $from
     * @return mixed
     */
    public function send($to, string $text, $is2FA, $from);
}