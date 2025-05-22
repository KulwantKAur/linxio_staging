<?php

namespace App\Service\Notification\Recipient\Interfaces\Type;

interface RecipientTypeInterface
{
    /**
     * @return string
     */
    public function getTypeRecipient(): string;
}
