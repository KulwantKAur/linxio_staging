<?php

namespace App\Service\Notification;

use App\Entity\Notification\NotificationRecipients;
use App\Entity\Notification\NotificationRecipients as Recipients;
use App\Entity\Notification\Transport;
use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Service\Notification\Recipient\Factory\RecipientTypeFactory;
use App\Service\Notification\Recipient\Interfaces\RecipientInterface;
use App\Service\Notification\Recipient\RecipientData;
use App\Service\Notification\Recipient\Transport\Recipient;
use App\Service\Notification\Recipient\Transport\RecipientNull;
use Doctrine\ORM\EntityManager;

class RecipientService
{
    private $em;
    private $roleRepository;
    private $userRepository;
    private $userGroupRepository;

    /** @var RecipientTypeFactory */
    private $recipientTypeFactory;

    /**
     * RecipientService constructor.
     * @param EntityManager $em
     * @param RecipientTypeFactory $recipientTypeFactory
     */
    public function __construct(EntityManager $em, RecipientTypeFactory $recipientTypeFactory)
    {
        $this->em = $em;
        $this->recipientTypeFactory = $recipientTypeFactory;
        $this->roleRepository = $this->em->getRepository(Role::class);
        $this->userRepository = $this->em->getRepository(User::class);
        $this->userGroupRepository = $this->em->getRepository(UserGroup::class);
    }

    /**
     * @param $recipient
     * @param Transport $transport
     * @return string|null
     */
    public function getRecipientDataByTransport($recipient, Transport $transport)
    {
        // TODO - temporary refactoring
        if ($recipient instanceof User) {
            $recipient = new Recipient($recipient, NotificationRecipients::TYPE_USERS_LIST);
        }

        try {
            return $this->getRecipientDataByTransportNew($recipient, $transport);
        } catch (\Exception $e) {
            return new RecipientNull();
        }
    }

    /**
     * @param RecipientInterface $recipient
     * @param Transport $transport
     * @return mixed
     */
    public function getRecipientDataByTransportNew(RecipientInterface $recipient, Transport $transport)
    {
        return ([
                Transport::TRANSPORT_SMS => static function ($user) {
                    return $user->getPhone() ?? 'default_phone';
                },
                Transport::TRANSPORT_EMAIL => static function ($user) {
                    return $user->getEmail() ?? 'default_email';
                },
                Transport::TRANSPORT_WEB_APP => static function ($user) {
                    return $user->getId() ?? 'default_web_app';
                },
                Transport::TRANSPORT_MOBILE_APP => static function ($user) {
                    return $user->getId() ?? 'default_mobile_app';
                },
            ][$transport->getAlias()] ?? static function () {
                return 'default';
            })($recipient);
    }

    /**
     * @param Notification $notification
     * @param $entity
     * @return array
     */
    public function getNotificationRecipients(Notification $notification, $entity)
    {
        $recipients = $notification->getRecipients();

        $allowedRecipients = [];
        foreach ($recipients as $recipient) {
            $recipient = $this->getRecipientHandler($notification, $recipient, $entity);
            if (!empty($recipient)) {
                $allowedRecipients[] = $recipient;
            }
        }
        $recipientsToCopy = $this->getRecipientHandlerToCopy($notification->getEvent(), $entity);

        $allowedRecipients = array_merge(
            $allowedRecipients,
            $recipientsToCopy
        );

        $result = [];
        foreach ($allowedRecipients as $recipient) {
            /** @var RecipientData $recipient */
            $result[] = $recipient;
        }

        return $result;
    }

    public function getRecipientHandler(Notification $notification, Recipients $recipient, $entity)
    {
        $recipientTypeClass = $this->recipientTypeFactory->getInstance($notification, $recipient, $entity);

        if (0 === \count($recipientTypeClass->getRecipients())) {
            return false;
        }

        return new RecipientData(
            $recipientTypeClass->getTypeRecipient(),
            $recipientTypeClass->getRecipients()
        );
    }

    /**
     * @param Event $event
     * @param $entity
     * @return object|object[]
     */
    public function getRecipientHandlerToCopy(Event $event, $entity)
    {
        if ($event->isDriverToRecipient() !== false) {
            switch ($event->getName()) {
                case Event::DIGITAL_FORM_IS_NOT_COMPLETED:
                    $ids = $entity->getVehicle() ?
                        ($entity->getVehicle()->getDriver() ? $entity->getVehicle()->getDriver()->getId() : null)
                        : null;

                    $recipients = [];
                    if (!is_null($ids)) {
                        $recipients = $this
                            ->userRepository
                            ->findBy(['id' => $ids, 'status' => User::STATUS_ACTIVE]);
                    }

                    return new RecipientData(Recipients::TYPE_USERS_LIST, $recipients);
                default:
                    return [];
            }
        }
        return [];
    }
}
