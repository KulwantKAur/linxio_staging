<?php

namespace App\Service\Notification\Recipient\Transport;

use App\Entity\User;
use App\Service\Notification\Recipient\Interfaces\Transport\RecipientEmailInterface;
use App\Service\Notification\Recipient\Interfaces\Transport\RecipientSmsInterface;
use App\Service\Notification\Recipient\Interfaces\Transport\RecipientWebAppInterface;
use App\Service\Notification\Recipient\Traits\RecipientEmailTrait;
use App\Service\Notification\Recipient\Traits\RecipientSmsTrait;

class Recipient implements
    RecipientEmailInterface,
    RecipientSmsInterface,
    RecipientWebAppInterface
{
    use RecipientEmailTrait;
    use RecipientSmsTrait;

    private string $type;
    private string $id;
    private string $email;
    private string $phone;
    private User $user;

    /**
     * Recipient constructor.
     * @param User $user
     * @param string $type
     */
    public function __construct(User $user, string $type = '')
    {
        $this->type = $type;
        $this->id = $user->getId();
        $this->email = $user->getEmail();
        $this->phone = $user->getPhone();
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getTypeRecipient(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }
}
