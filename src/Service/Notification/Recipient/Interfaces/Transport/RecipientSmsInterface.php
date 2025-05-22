<?php

namespace App\Service\Notification\Recipient\Interfaces\Transport;

use App\Service\Notification\Recipient\Interfaces\RecipientInterface;

interface RecipientSmsInterface extends RecipientInterface
{
    /**
     * @return string
     */
    public function getPhone(): string;
}
