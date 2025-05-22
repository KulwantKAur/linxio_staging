<?php

namespace App\Service\Notification;

use App\Entity\Asset;
use App\Entity\Notification\Message;
use App\Entity\Notification\Notification;
use App\Entity\Notification\NotificationRecipients;
use App\Entity\Notification\Template;
use App\Entity\EventLog\EventLog;
use App\Entity\Notification\Transport;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Service\BaseService;
use App\Service\Notification\Recipient\RecipientData;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\Pagination\PaginationInterface;

class MessageService extends BaseService
{
    private $em;
    private $templateService;
    private $recipientService;
    private $importanceService;
    private $transportService;
    private $messageRepository;
    private $transportRepository;
    private $acknowledgeService;

    private const BATCH_SIZE = 20;

    private function handleMessageDuplicates(
        Message $message,
        Notification $notification,
        \DateTime $occurrenceDate
    ): Message {
        $existingMessageCount = $this->em->getRepository(Message::class)->findMessageDuplicatesCount(
            $notification,
            $occurrenceDate,
            $message->getTransportType(),
            $message->getRecipient(),
            $message->getBody()
        );

        if ($existingMessageCount > 0) {
            $message->setStatus(Message::TYPE_DUPLICATED);
        }

        return $message;
    }

    public function __construct(
        EntityManager $em,
        TemplateService $templateService,
        RecipientService $recipientService,
        ImportanceService $importanceService,
        TransportService $transportService,
        AcknowledgeService $acknowledgeService,
        private readonly EntityPlaceholderService $placeholderService
    ) {
        $this->em = $em;
        $this->templateService = $templateService;
        $this->recipientService = $recipientService;
        $this->importanceService = $importanceService;
        $this->transportService = $transportService;
        $this->acknowledgeService = $acknowledgeService;
        $this->messageRepository = $em->getRepository(Message::class);
        $this->transportRepository = $em->getRepository(Transport::class);
    }

    public function createNotificationMessages(
        Notification $notification,
        ?EventLog $eventLog,
        array $recipients,
//        array $placeholders,
        \DateTime $dt,
        $event,
        $entity,
        $context
    ) {
        $allowedTransports = $this->transportService->getNotificationTransports($notification);

        if (0 === \count($allowedTransports)) {
            return;
        }

//        $messages = $this->templateService->getPreparedTemplates(
//            $notification->getOwnerTeam(),
//            $notification,
//            $allowedTransports,
//            $placeholders
//        );
//
//        if (0 === \count($messages)) {
//            return;
//        }

        foreach ($recipients as $recipient) {
//            $transports = $this->checkTransportByRecipientType($recipient, $messages);

            foreach ($recipient->getValue() as $user) {
                $userEntity = method_exists($user, 'getUser') && $user?->getUser() instanceof User
                    ? $user?->getUser() : null;

                $placeholders = $this->placeholderService->generatePlaceholders($event, $entity, $context, $userEntity);
                $messages = $this->templateService->getPreparedTemplates(
                    $notification->getOwnerTeam(),
                    $notification,
                    $allowedTransports,
                    $placeholders
                );

                if (0 === \count($messages)) {
                    return;
                }

                $transports = $this->checkTransportByRecipientType($recipient, $messages);

                foreach ($transports as [$message, $template]) {
                    if ($userEntity) {
                        $message['timezone'] = 'Timezone: ' . $userEntity->getTimezoneText();
                    } else {
                        $message['timezone'] = 'Timezone: ' . $notification->getOwnerTeam()->getTimezoneText();
                    }

                    $obj = new Message();
                    $obj->setBody($message);
                    $obj->setRecipient(
                        $this->recipientService->getRecipientDataByTransport($user, $template->getTransport())
                    );
                    $obj->setTransportType($template->getTransport()->getAlias());
                    $obj->setStatus(Message::TYPE_PENDING);
                    $obj->setSendingTime(
                        $this->importanceService->calculateSendTime($notification, $template->getTransport())
                    );
                    $obj->setOccurrenceTime($dt);
                    $obj->setProcessingTime(new \DateTime());
                    $obj->setEventLog($eventLog);
                    $obj->setNotification($notification);
                    $obj->setSender(SenderService::getSenderByTeamAndTransport($notification->getOwnerTeam(),
                        $template->getTransport()));

                    $obj = $this->handleMessageDuplicates($obj, $notification, $dt);
                    $this->em->persist($obj);
                }
            }
        }

        if ($notification->hasAcknowledge()) {
            $this->acknowledgeService->createAcknowledge($eventLog, $notification);
        }

        $this->em->flush();
    }

    public function checkTransportByRecipientType(RecipientData $recipient, array $messages)
    {
        $transportSettings = Transport::TRANSPORT_TYPE_TO_RECIPIENT_TYPE;

        return match ($recipient->getTypeRecipient()) {
            NotificationRecipients::TYPE_OTHER_EMAIL, NotificationRecipients::TYPE_OTHER_PHONE => \array_filter(
                $messages,
                static function ($transport) use ($transportSettings, $recipient) {
                    return $transport[1]
                            ->getTransport()
                            ->getName() === $transportSettings[$recipient->getTypeRecipient()];
                }
            ),
            default => $messages,
        };
    }

    /**
     * @param array $params
     * @param User $user
     * @return mixed
     * @throws \Exception
     */
    public function getMessages(array $params, User $user)
    {
        /** @var Transport $transport */
        $transport = isset($params['transport'])
            ? $this->getTransport($params['transport'])
            : $this->getTransport(Transport::TRANSPORT_WEB_APP);

        $recipient = $this->recipientService->getRecipientDataByTransport($user, $transport);
        $status = $params['status'] ?? Message::TYPE_DELIVERY;
        $vehicleId = $params['vehicleId'] ?? null;

        return $this->messageRepository->findMessages($recipient, $transport, $status, $vehicleId);
    }

    /**
     * @param array $data
     * @param Message $message
     * @return Message
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function markAsReadMessagesById(array $data, Message $message): Message
    {
        $data['updatedAt'] = new \DateTime();
        $data['isRead'] = $data['isRead'] ?? true;
        $message->setAttributes($data);

        $this->em->flush();
        $this->em->refresh($message);

        return $message;
    }

    /**
     * @param array $data
     * @param User $user
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\Persistence\Mapping\MappingException
     */
    public function markAsReadsAllMessagesByUser(array $data, User $user)
    {
        $transport = $data['transport'] ?? [Transport::TRANSPORT_WEB_APP, Transport::TRANSPORT_MOBILE_APP];
        $status = $data['status'] ?? Message::TYPE_DELIVERY;
        $recipient = $user->getId();

        $counter = 0;
        foreach ($this->messageRepository->findUnreadMessages($recipient, $status, $transport, true) as [$message]) {
            $data['updatedBy'] = $user;
            $data['updatedAt'] = new \DateTime();
            $data['isRead'] = $data['isRead'] ?? true;

            /** @var Message $message */
            $message->setAttributes($data);

            if (($counter % self::BATCH_SIZE) === 0) {
                $this->em->flush();
                $this->em->clear();
            }
            ++$counter;
        }
        $this->em->flush();
    }

    /**
     * @param array $params
     * @param User $user
     * @return mixed
     */
    public function getCountUnreadMessages(array $params, User $user)
    {
        /** @var Transport $transport */
        $transport = isset($params['transport'])
            ? $this->getTransport($params['transport'])
            : $this->getTransport(Transport::TRANSPORT_WEB_APP);

        $recipient = $this->recipientService->getRecipientDataByTransport($user, $transport);
        $status = $params['status'] ?? Message::TYPE_DELIVERY;

        $query = $this->messageRepository->countUnreadMessages($recipient, $transport, $status);

        return $query;
    }

    /**
     * @param int $id
     * @return Message|null
     */
    public function getById(int $id): ?Message
    {
        $message = $this->messageRepository->find($id);

        return !empty($message) ? $message : null;
    }

    /**
     * @param string $transport
     * @return object|null
     */
    public function getTransport(string $transport)
    {
        return $this->transportRepository->findOneBy(['name' => $transport]);
    }

    /**
     * @param PaginationInterface $pagination
     * @return array
     * @throws \Exception
     */
    public function formatMessages(PaginationInterface $pagination): array
    {
        $data = [];
        foreach ($pagination->getItems() as $entity) {
            /** @var Message $entity */
            $data[] = $entity->toArray();
        }
        return $data;
    }

    /**
     * @param array $params
     * @param User $user
     * @param Vehicle $vehicle
     * @return \Doctrine\ORM\Query
     */
    public function getMessagesByVehicle(array $params, User $user, Vehicle $vehicle)
    {
        $dateFrom = isset($params['startDate'])
            ? self::parseDateToUTC($params['startDate']) : (new Carbon())->subHours(24);
        $dateTo = isset($params['endDate'])
            ? self::parseDateToUTC($params['endDate']) : Carbon::now();

        /** @var Transport $transport */
        $transport = $this->getTransport(Transport::TRANSPORT_WEB_APP);

        $recipient = !$user->isAdminClient()
            ? $this->recipientService->getRecipientDataByTransport($user, $transport) : null;

        $status = $params['status'] ?? Message::TYPE_DELIVERY;
        $vehicleId = $vehicle->getId() ?? null;

        return $this->messageRepository->findMessagesByVehicle(
            $dateFrom,
            $dateTo,
            $transport,
            $status,
            $vehicleId,
            $user->getTeamId(),
            $recipient
        );
    }

    /**
     * @param array $params
     * @param User $user
     * @param Asset $asset
     * @return \Doctrine\ORM\Query
     */
    public function getMessagesByAsset(array $params, User $user, Asset $asset)
    {
        $dateFrom = isset($params['startDate'])
            ? self::parseDateToUTC($params['startDate']) : (new Carbon())->subHours(24);
        $dateTo = isset($params['endDate'])
            ? self::parseDateToUTC($params['endDate']) : Carbon::now();

        /** @var Transport $transport */
        $transport = $this->getTransport(Transport::TRANSPORT_WEB_APP);

        $recipient = $this->recipientService->getRecipientDataByTransport($user, $transport);
        $status = $params['status'] ?? Message::TYPE_DELIVERY;
        $assetId = $asset->getId() ?? null;

        return $this->messageRepository->findMessagesByAsset(
            $dateFrom,
            $dateTo,
            $recipient,
            $transport,
            $status,
            $assetId
        );
    }
}
