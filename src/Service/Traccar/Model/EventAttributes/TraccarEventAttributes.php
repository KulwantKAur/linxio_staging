<?php

namespace App\Service\Traccar\Model\EventAttributes;

use App\Entity\Device;

class TraccarEventAttributes
{
    public ?string $alarm;

    /**
     * @param \stdClass|array $fields
     */
    public function __construct($fields)
    {
        $this->alarm = $fields->alarm ?? null;
    }

    /**
     * @param string $protocol
     * @param $data
     * @param Device $device
     * @return TraccarEventAttributes
     * @throws \Exception
     */
    public static function getInstance(string $protocol, $data, ?Device $device): TraccarEventAttributes
    {
        switch ($protocol) {
            default:
                return new self($data);
        }
    }

    /**
     * @return string|null
     */
    public function getAlarm(): ?string
    {
        return $this->alarm;
    }
}

