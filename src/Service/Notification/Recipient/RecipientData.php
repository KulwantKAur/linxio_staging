<?php

namespace App\Service\Notification\Recipient;

use App\Service\Notification\Recipient\Interfaces\RecipientInterface;

class RecipientData implements RecipientInterface
{
    private string $type;
    private array $value;

    /**
     * RecipientData constructor.
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
     * @return array
     */
    public function getValue(): array
    {
        return $this->value;
    }
}
