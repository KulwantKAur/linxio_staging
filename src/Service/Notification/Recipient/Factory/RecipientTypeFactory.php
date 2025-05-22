<?php

namespace App\Service\Notification\Recipient\Factory;

use App\Entity\Notification\Notification;
use App\Entity\Notification\NotificationRecipients;
use App\Service\Notification\Recipient\Type\RecipientTypeOtherEmail;
use App\Service\Notification\Recipient\Type\RecipientTypeOtherPhone;
use App\Service\Notification\Recipient\Type\RecipientTypeRole;
use App\Service\Notification\Recipient\Type\RecipientTypeSelf;
use App\Service\Notification\Recipient\Type\RecipientTypeUser;
use App\Service\Notification\Recipient\Type\RecipientTypeUserGroup;
use Doctrine\ORM\EntityManager;
use Symfony\Contracts\Translation\TranslatorInterface;

class RecipientTypeFactory
{
    protected static $availableRecipientType = [
        NotificationRecipients::TYPE_SELF => RecipientTypeSelf::class,
        NotificationRecipients::TYPE_ROLE => RecipientTypeRole::class,
        NotificationRecipients::TYPE_USERS_LIST => RecipientTypeUser::class,
        NotificationRecipients::TYPE_USER_GROUPS_LIST => RecipientTypeUserGroup::class,
        NotificationRecipients::TYPE_OTHER_EMAIL => RecipientTypeOtherEmail::class,
        NotificationRecipients::TYPE_OTHER_PHONE => RecipientTypeOtherPhone::class,
    ];

    /** @var EntityManager */
    protected $em;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * RecipientTypeFactory constructor.
     * @param EntityManager $em
     * @param TranslatorInterface $translator
     */
    public function __construct(EntityManager $em, TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
    }

    /**
     * @param Notification $notification
     * @param NotificationRecipients $recipient
     * @param $entity
     * @return mixed
     * @throws \Exception
     */
    public function getInstance(Notification $notification, NotificationRecipients $recipient, $entity)
    {
        $recipientType = $recipient->getType();

        if (!array_key_exists($recipientType, self::$availableRecipientType)) {
            $message = 'Unsupported recipient type: ' . $recipientType;
            throw new \Exception($message);
        }

        $recipientTypeClass = self::$availableRecipientType[$recipientType];

        return (new $recipientTypeClass($this->em, $notification, $recipient, $entity));
    }
}
