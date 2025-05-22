<?php

namespace App\Service\Notification\Recipient\Interfaces\Transport;

use App\Service\Notification\Recipient\Interfaces\RecipientInterface;

interface RecipientWebAppInterface extends RecipientInterface
{
    /**
     * @return string
     */
    public function getId(): string;
}
