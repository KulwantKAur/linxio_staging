<?php

namespace App\Mailer;

abstract class Mailer
{
    abstract public function send($from, $to, $subject, $body);
}