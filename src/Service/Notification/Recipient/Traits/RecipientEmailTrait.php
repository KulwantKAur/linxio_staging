<?php

namespace App\Service\Notification\Recipient\Traits;

trait RecipientEmailTrait
{
    private string $email;

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }
}
