<?php

namespace App\Service\Notification\Recipient\Type;

use App\Entity\Notification\Notification;
use App\Entity\Notification\NotificationRecipients;
use App\Service\Notification\Recipient\Interfaces\RecipientInterface;
use App\Service\Notification\Recipient\Transport\Recipient;
use Doctrine\ORM\EntityManager;

class RecipientTypeSelf implements RecipientInterface
{
    private EntityManager $em;
    private Notification $notification;
    private NotificationRecipients $recipient;
    private $entity;

    /**
     * RecipientTypeSelf constructor.
     * @param EntityManager $em
     * @param Notification $notification
     * @param NotificationRecipients $recipient
     * @param $entity
     */
    public function __construct(
        EntityManager $em,
        Notification $notification,
        NotificationRecipients $recipient,
        $entity
    ) {
        $this->em = $em;
        $this->notification = $notification;
        $this->recipient = $recipient;
        $this->entity = $entity;
    }

    /**
     * @return string
     */
    public function getTypeRecipient(): string
    {
        return NotificationRecipients::TYPE_SELF;
    }

    /**
     * @return array
     */
    public function getRecipients(): array
    {
        $recipients = [];
        if (!empty($this->entity)) {
            $recipients[] = new Recipient($this->entity, $this->getTypeRecipient());
        }

        return $recipients;
    }
}
