<?php

namespace App\Service\Tracker\Interfaces;

interface DateTimePartPayloadInterface
{
    /**
     * @param string $payload
     * @param string $dtString
     * @return string
     */
    public function getPayloadWithNewDateTime(string $payload, string $dtString): string;

    /**
     * @param string $payload
     * @return string
     */
    public function getDateTimePayload(string $payload): string;
}
