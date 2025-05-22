<?php

namespace App\Service\Notification\Recipient\Transport;

use App\Service\Notification\Recipient\Interfaces\Transport\RecipientEmailInterface;
use App\Service\Notification\Recipient\Traits\RecipientEmailTrait;

class RecipientOtherEmail implements RecipientEmailInterface
{
    use RecipientEmailTrait;

    private string $type;
    private string $email;

    /**
     * RecipientOtherEmail constructor.
     * @param string $email
     * @param string $type
     */
    public function __construct(string $email = '', string $type = '')
    {
        $this->type = $type;
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getTypeRecipient(): string
    {
        return $this->type;
    }
}
