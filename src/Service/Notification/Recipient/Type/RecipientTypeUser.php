<?php

namespace App\Service\Notification\Recipient\Type;

use App\Entity\Notification\Notification;
use App\Entity\Notification\NotificationRecipients;
use App\Entity\Role;
use App\Entity\User;
use App\Service\Notification\Recipient\Interfaces\RecipientInterface;
use App\Service\Notification\Recipient\Transport\Recipient;
use Doctrine\ORM\EntityManager;

class RecipientTypeUser implements RecipientInterface
{
    private EntityManager $em;
    private Notification $notification;
    private NotificationRecipients $recipient;
    private $entity;

    private $roleRepository;
    private $userRepository;

    /**
     * RecipientTypeUser constructor.
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

        $this->roleRepository = $this->em->getRepository(Role::class);
        $this->userRepository = $this->em->getRepository(User::class);
    }

    /**
     * @return string
     */
    public function getTypeRecipient(): string
    {
        return NotificationRecipients::TYPE_USERS_LIST;
    }

    /**
     * @return array
     */
    public function getRecipients(): array
    {
        $team = $this->notification->getOwnerTeam();
        $ids = array_filter($this->recipient->getValue());
        $recipients = [];

        if (0 !== \count($ids)) {
            $users = $this->userRepository
                ->findBy(['team' => $team, 'id' => $ids, 'status' => [User::STATUS_ACTIVE, User::STATUS_NEW]]);

            foreach ($users as $recipient) {
                if (!empty($recipient)) {
                    /** @var User $recipient */
                    $recipients[] = new Recipient($recipient, $this->getTypeRecipient());
                }
            }
        }

        return $recipients;
    }
}
