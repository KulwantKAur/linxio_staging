<?php

namespace App\Service\Notification\Recipient\Transport;

use App\Service\Notification\Recipient\Interfaces\RecipientInterface;

class RecipientNull implements RecipientInterface
{
    private string $type;
    private array $value;

    /**
     * NoRecipient constructor.
     * @param string $type
     * @param array $value
     */
    public function __construct(string $type = '', array $value = [])
    {
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getTypeRecipient(): string
    {
        return $this->type;
    }

    /**
     * @return null
     */
    public function getValue()
    {
        return '';
    }
}
