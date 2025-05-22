<?php

namespace App\Service\Notification\Recipient\Transport;

use App\Service\Notification\Recipient\Interfaces\Transport\RecipientSmsInterface;
use App\Service\Notification\Recipient\Traits\RecipientSmsTrait;

class RecipientOtherPhone implements RecipientSmsInterface
{
    use RecipientSmsTrait;

    private string $type;
    private string $phone;

    /**
     * RecipientOtherPhone constructor.
     * @param string $phone
     * @param string $type
     */
    public function __construct(string $phone = '', string $type = '')
    {
        $this->type = $type;
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getTypeRecipient(): string
    {
        return $this->type;
    }
}
