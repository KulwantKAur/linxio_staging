<?php

namespace App\Service\Notification\Recipient\Type;

use App\Entity\Notification\Notification;
use App\Entity\Notification\NotificationRecipients;
use App\Service\Notification\Recipient\Interfaces\RecipientInterface;
use App\Service\Notification\Recipient\Transport\RecipientOtherPhone;
use Doctrine\ORM\EntityManager;

class RecipientTypeOtherPhone implements RecipientInterface
{
    private EntityManager $em;
    private Notification $notification;
    private NotificationRecipients $recipient;
    private $entity;

    /**
     * RecipientTypeOtherPhone constructor.
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
        return NotificationRecipients::TYPE_OTHER_PHONE;
    }

    /**
     * @return array
     */
    public function getRecipients(): array
    {
        $ids = $this->recipient->getValue();

        $recipients = [];
        foreach ($ids as $element) {
            if (!empty($element)) {
                $recipients[] = new RecipientOtherPhone($element, $this->getTypeRecipient());
            }
        }

        return $recipients;
    }
}


