<?php

namespace App\Service\Notification\Recipient\Interfaces\Transport;

use App\Service\Notification\Recipient\Interfaces\RecipientInterface;

interface RecipientEmailInterface extends RecipientInterface
{
    /**
     * @return string
     */
    public function getEmail(): string;
}
