<?php

namespace App\Service\Notification\Recipient\Traits;

trait RecipientSmsTrait
{
    private string $phone;

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }
}
