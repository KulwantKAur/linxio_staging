<?php

namespace App\Service\Chat;

use App\Controller\BaseController;
use App\Entity\Chat;
use App\Entity\ChatHistory;
use App\Entity\ChatHistoryUnread;
use App\Entity\Notification\Message;
use App\Entity\Notification\NotificationMobileDevice;
use App\Entity\Permission;
use App\Entity\Setting;
use App\Entity\User;
use App\Events\Chat\ChatSendNotificationEvent;
use App\Exceptions\ValidationException;
use App\Service\BaseService;
use App\Service\ElasticSearch\ElasticSearch;
use App\Service\File\FileServiceInterface;
use App\Service\Firebase\FCMService;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Setting\SettingService;
use App\Service\User\UserService;
use App\Util\ExceptionHelper;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\File as FileConstraint;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class ChatService extends BaseService implements ChatServiceInterface
{
    public const EVENT_CHAT_CREATED = 'chatCreated';
    public const EVENT_CHAT_EDITED = 'chatEdited';
    public const EVENT_CHAT_DELETED = 'chatDeleted';
    public const EVENT_MESSAGE_SENT = 'messageSent';
    public const EVENT_MESSAGE_EDITED = 'messageEdited';
    public const EVENT_MESSAGE_READ = 'messageRead';
    public const EVENT_MESSAGE_DELETED = 'messageDeleted';
    public const EVENT_USERS_DELETED_FROM_CHAT = 'usersDeletedFromChat';
    public const EVENT_USERS_ADDED_TO_CHAT = 'usersAddedToChat';
    public const EVENT_USER_STATUS_UPDATED = 'userStatusUpdated';

    public const ELASTIC_SIMPLE_FIELDS = [
        'id' => 'id',
        'name' => 'name',
        'key' => 'key',
        'createdAt' => 'createdAt',
        'updatedAt' => 'updatedAt',
        'teamIds' => 'createdBy.teamId',
        'users' => 'users.id',
        'userIds' => 'users.id',
        'message' => 'message',
        'lastSentAt' => 'lastSentAt',
        'chat.id' => 'chat.id',
        'sortDate' => 'sortDate',
        'scope' => 'scope',
    ];

    public EntityManagerInterface $em;
    public UserService $userService;
    public TranslatorInterface $translator;
    public ValidatorInterface $validator;
    public ElasticSearch $chatFinder;
    public PaginatorInterface $paginator;
    public EventDispatcherInterface $eventDispatcher;
    public NotificationEventDispatcher $notificationDispatcher;
    public LoggerInterface $logger;
    public FileServiceInterface $fileService;
    public SettingService $settingService;
    private FCMService $fcmService;

    /**
     * @param array $userIds
     * @return string|null
     */
    abstract public function getUniqueChatChannel(array $userIds): ?string;

    /**
     * @param User $user
     * @return string|null
     */
    abstract public function getPrivateChannel(User $user): ?string;

    /**
     * @param User $user
     * @return string|null
     */
    abstract public function getTeamChannel(User $user): ?string;

    /**
     * @param Chat $chat
     * @return array
     */
    abstract public function getChatHistoryFromSource(Chat $chat): array;

    /**
     * @param Chat $chat
     * @param ChatHistory $chatHistory
     */
    abstract public function sendMessage(Chat $chat, ChatHistory $chatHistory);

    /**
     * @param string $channel
     * @param array $data
     * @return string|null
     */
    abstract public function publish(string $channel, array $data);

    /**
     * @param Chat $chat
     * @param User $user
     * @throws \Doctrine\ORM\ORMException
     */
    private function removeUserUnreadMessages(Chat $chat, User $user)
    {
        $unreadHistoriesByUser = $chat->getUnreadHistoriesByUser($user);

        foreach ($unreadHistoriesByUser as $unreadHistoryByUser) {
            $unreadHistoriesByUser->removeElement($unreadHistoryByUser);
            $this->em->remove($unreadHistoryByUser);
        }
    }

    /**
     * @param UploadedFile $file
     * @param User $currentUser
     * @throws ValidationException
     */
    private function validateFileLimitByUserTeam(UploadedFile $file, User $currentUser)
    {
        $errors = [];
        $uploadFileLimitSetting = $this->settingService
            ->getTeamSettingValueByKey($currentUser->getTeam(), Setting::MESSENGER_UPLOAD_FILE_LIMIT);
        $constraint = $this->validator->validate($file, new FileConstraint([
            'maxSize' => $uploadFileLimitSetting['value'],
        ]));

        if (count($constraint)) {
            $errors['attachment'] = $constraint->get(0)->getMessage();
        }
        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    /**
     * @param EntityManager $em
     * @param EventDispatcherInterface $eventDispatcher
     * @param NotificationEventDispatcher $notificationDispatcher
     * @param LoggerInterface $logger
     * @param TransformedFinder $chatFinder
     * @param PaginatorInterface $paginator
     * @param TranslatorInterface $translator
     * @param ValidatorInterface $validator
     * @param UserService $userService
     * @param FileServiceInterface $fileService
     * @param SettingService $settingService,
     * @param FCMService $fcmService
     */
    public function __construct(
        EntityManager $em,
        EventDispatcherInterface $eventDispatcher,
        NotificationEventDispatcher $notificationDispatcher,
        LoggerInterface $logger,
        TransformedFinder $chatFinder,
        PaginatorInterface $paginator,
        TranslatorInterface $translator,
        ValidatorInterface $validator,
        UserService $userService,
        FileServiceInterface $fileService,
        SettingService $settingService,
        FCMService $fcmService,
    ) {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->logger = $logger;
        $this->chatFinder = new ElasticSearch($chatFinder);
        $this->paginator = $paginator;
        $this->userService = $userService;
        $this->translator = $translator;
        $this->validator = $validator;
        $this->fileService = $fileService;
        $this->settingService = $settingService;
        $this->fcmService = $fcmService;
    }

    /**
     * @param array $users
     * @return string|null
     */
    public function getChatName(array $users): ?string
    {
        $userFullNames = array_map(function (User $user) {
            return $user->getFullName();
        }, $users);
        $name = implode(', ', $userFullNames);

        return strlen($name) > Chat::NAME_MAX_LENGTH ? substr($name, 0, Chat::NAME_MAX_LENGTH) : $name;
    }

    /**
     * @param int $id
     * @return Chat|null
     */
    public function getById(int $id): ?Chat
    {
        return $this->em->getRepository(Chat::class)->find($id);
    }

    /**
     * @param string $channel
     * @return Chat|null
     */
    public function getByChannel(string $channel): ?Chat
    {
        return $this->em->getRepository(Chat::class)->getChatByChannel($channel);
    }

    /**
     * @param string $channel
     * @return Chat|null
     */
    public function getByChannelMask(string $channel): ?Chat
    {
        return $this->em->getRepository(Chat::class)->getChatByChannelMask($channel);
    }

    /**
     * @param int $id
     * @return ChatHistory|null
     */
    public function getChatMessageById(int $id): ?ChatHistory
    {
        return $this->em->getRepository(ChatHistory::class)->find($id);
    }

    /**
     * @param array $params
     * @param User $currentUser
     * @return Chat
     * @throws ValidationException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createChat(array $params, User $currentUser)
    {
        $userIds = $params['userIds'] ?? null;
        $chatName = $params['name'] ?? null;
        $users = [];
        $chat = new Chat();

        foreach ($userIds as $userId) {
            $user = $this->userService->get($userId);
            $chat->addUser($user);
            $users[] = $user;
        }

        $chat->addUser($currentUser);
        $users[] = $currentUser;

        if (!in_array($currentUser->getId(), $userIds)) {
            $userIds[] = $currentUser->getId();
        }
        if (count($users) == 2) {
            $chat->setIsIndividual(true);
            $existingChat = $this->em->getRepository(Chat::class)->getPersonalChatByUserIds($userIds);

            if ($existingChat) {
                return $existingChat;
            }
        }

        $chat->setChannel($this->getUniqueChatChannel($userIds));
        $chat->setCreatedBy($currentUser);
        $chat->setLeader($currentUser);
        $chat->setName($chatName);
        $this->validate($this->validator, $chat);
        $this->em->persist($chat);
        $this->em->flush();
        $this->notifyChatUsersForNewChat($chat, $currentUser);

        return $chat;
    }

    /**
     * @param Chat $chat
     * @param array $params
     * @param User $currentUser
     * @return Chat
     * @throws ValidationException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function editChat(Chat $chat, array $params, User $currentUser)
    {
        if (array_key_exists('name', $params)) {
            $chat->setName($params['name']);
        }

        $chat->setUpdatedBy($currentUser);
        $chat->setUpdatedAt(new \DateTime());
        $this->validate($this->validator, $chat);
        $this->em->flush();
        $this->notifyChatUsersForChatEdited($chat, $currentUser);
        $this->sendSystemMessageChatRenamed($chat, $currentUser);

        return $chat;
    }

    /**
     * @param Chat $chat
     * @param array $params
     * @param User $currentUser
     * @return Chat
     * @throws ValidationException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function editChatUsers(Chat $chat, array $params, User $currentUser)
    {
        $userIds = $params['userIds'] ?? [];
        $usersToAdd = [];
        $usersToDelete = [];
        $oldUserIds = $chat->getUserIds();
        $idsToDelete = array_diff($oldUserIds, $userIds);
        $idsToAdd = array_diff($userIds, $oldUserIds);

        foreach ($idsToDelete as $userId) {
            $userToDelete = $this->userService->get($userId);
            $usersToDelete[] = $userToDelete;
            $this->removeUserUnreadMessages($chat, $userToDelete);
            $chat->removeUser($userToDelete);
        }
        foreach ($idsToAdd as $userId) {
            $userToAdd = $this->userService->get($userId);

            if (!$chat->getUsers()->contains($userToAdd)) {
                $usersToAdd[] = $userToAdd;
            }

            $chat->addUser($userToAdd);
        }
        if ($idsToAdd || $idsToDelete) {
            $chat->setIsIndividual(false);
        }

        $chat->setUpdatedBy($currentUser);
        $chat->setUpdatedAt(new \DateTime());
        $chat->setChannel($userIds ? $this->getUniqueChatChannel($userIds) : $chat->getChannel());
        $newUsers = $chat->getUsers()->getValues();
        $this->em->flush();
        $this->updateChatLeader($chat, $userIds, $idsToDelete);
        $this->notifyUsersDeletedFromChat($chat, $currentUser, array_merge($newUsers, $usersToDelete), $usersToDelete);
        $this->sendSystemMessageUsersDeletedFromChat($chat, $currentUser, $usersToDelete);
        $this->notifyUsersAddedToChat($chat, $currentUser, $newUsers, $usersToAdd);
        $this->sendSystemMessageUsersAddedToChat($chat, $currentUser, $usersToAdd);
        $this->notifyChatUsersForChatEdited($chat, $currentUser);

        if (count($newUsers) < 1) {
            return $this->deleteChat($chat, $currentUser);
        }

        return $chat;
    }

    /**
     * @param Chat $chat
     * @param User $currentUser
     * @return Chat
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteChat(Chat $chat, User $currentUser)
    {
        $this->notifyChatUsersForChatDeleted($chat, $currentUser);
        $this->em->remove($chat);
        $this->em->flush();

        return $chat;
    }

    /**
     * @param Chat $chat
     * @param array $params
     * @param User $currentUser
     * @return ChatHistory
     * @throws ValidationException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createChatMessage(Chat $chat, array $params, User $currentUser): ChatHistory
    {
        $chatHistory = new ChatHistory($params);
        $chatHistory->setUser($currentUser);
        $chatHistory->setChat($chat);
        $chatHistory->setLocation($params['location'] ?? null);

        if ($attachment = $params['attachment'] ?? null) {
            $this->validateFileLimitByUserTeam($attachment, $currentUser);
            $file = $this->fileService->uploadChatAttachment($attachment, $chat, $currentUser);
            $chatHistory->setAttachment(null);
            $chatHistory->setFile($file);
        }

        $this->validate($this->validator, $chatHistory);
        $chat->setLastChatHistory($chatHistory);
        $this->em->persist($chatHistory);
        $this->em->flush();
        $this->notifyChatUsersForNewMessage($chat, $chatHistory, $currentUser);

//        $this->sendNotification($chat, $chatHistory, $currentUser);
        $this->eventDispatcher->dispatch(
            new ChatSendNotificationEvent($chat, $chatHistory, $currentUser),
            ChatSendNotificationEvent::CHAT_SENT_NTF
        );

        return $chatHistory;
    }

    /**
     * @param ChatHistory $chatHistory
     * @param array $params
     * @param User $currentUser
     * @return ChatHistory
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function editChatMessage(ChatHistory $chatHistory, array $params, User $currentUser): ChatHistory
    {
        $isMessageRead = false;
        $isMessageEdited = false;
        $chat = $chatHistory->getChat();

        if (isset($params['isRead'])) {
            $isMessageRead = true;
            $chatHistoryUnread = $chatHistory->getChatHistoryUnreadByUser($currentUser);

            if ($chatHistoryUnread) {
                $chatHistoryUnreadList = $chatHistory->getChat()
                    ->getUnreadHistoriesByUserAndChatHistoryUnread($currentUser, $chatHistoryUnread);

                foreach ($chatHistoryUnreadList as $chatHistoryUnread) {
                    $chat->removeUserUnreadMessage($currentUser, $chatHistoryUnread);
                    $chatHistory->removeChatHistoryUnread($chatHistoryUnread);
                    $this->em->remove($chatHistoryUnread);
                }
            }
        }
        if (isset($params['message'])) {
            $isMessageEdited = true;
            $chatHistory->setMessage($params['message']);
            $chatHistory->setUpdatedAt(new \DateTime());
        }

        $this->em->flush();

        if ($isMessageRead) {
            $this->notifyChatUsersForMessageRead($chatHistory->getChat(), $chatHistory, $currentUser);
        }
        if ($isMessageEdited) {
            $this->notifyChatUsersForMessageEdited($chatHistory->getChat(), $chatHistory, $currentUser);
        }

        return $chatHistory;
    }

    /**
     * @param ChatHistory $chatHistory
     * @param User $currentUser
     * @return ChatHistory
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteChatMessage(ChatHistory $chatHistory, User $currentUser): ChatHistory
    {
        if ($chatHistory->getFile()) {
            $this->fileService->delete($chatHistory->getFile());
            $chatHistory->setFile(null);
        }

        $message = $this->translator->trans(
            'entities.chat.current_user_removed_message', ['%currentUser%' => $currentUser->getFullName()]
        );
        $chatHistory->setMessage($message);
        $chatHistory->setLocation(null);
        $chatHistory->setType(ChatHistory::TYPE_SYSTEM);
        $chatHistory->setEvent(ChatHistory::EVENT_MESSAGE_DELETED_ID);
        $chatHistory->setEventSource($chatHistory->getId());
        $chatHistory->getChatHistoryUnread()->forAll(function($key, ChatHistoryUnread $chatHistoryUnread) {
            $this->em->remove($chatHistoryUnread);
            return true;
        });

        $this->notifyChatUsersForMessageDeleted($chatHistory->getChat(), $chatHistory, $currentUser);
        $this->em->flush();

        return $chatHistory;
    }

    /**
     * @param ChatHistory $chatHistory
     * @return ChatHistory
     * @throws \Exception
     */
    public function deleteChatMessageAttachmentByJob(ChatHistory $chatHistory): ChatHistory
    {
        if ($chatHistory->getFile()) {
            $this->fileService->deleteChatAttachmentByJob($chatHistory->getFile());
            $chatHistory->setUpdatedAt(new \DateTime());
            $this->em->flush();
            $this->notifyChatUsersForMessageEdited($chatHistory->getChat(), $chatHistory, null);
        }

        return $chatHistory;
    }

    /**
     * @param ChatHistory $chatHistory
     * @param User $currentUser
     * @return ChatHistory
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteChatMessageAttachment(
        ChatHistory $chatHistory,
        User $currentUser
    ): ChatHistory {
        if ($chatHistory->getFile()) {
            $this->fileService->delete($chatHistory->getFile());
            $chatHistory->setFile(null);

            if ($chatHistory->isEmptyMessage()) {
                $chatHistory = $this->deleteChatMessage($chatHistory, $currentUser);
            } else {
                $chatHistory->setUpdatedAt(new \DateTime());
                $this->notifyChatUsersForMessageEdited($chatHistory->getChat(), $chatHistory, $currentUser);
            }

            $this->em->flush();
        }

        return $chatHistory;
    }

    /**
     * @param ChatHistory $chatHistory
     * @param array $params
     * @param User $currentUser
     * @return ChatHistory
     * @throws ValidationException
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function addChatMessageAttachment(ChatHistory $chatHistory, array $params, User $currentUser): ChatHistory
    {
        if ($attachment = $params['attachment'] ?? null) {
            $chat = $chatHistory->getChat();
            $this->validateFileLimitByUserTeam($attachment, $currentUser);
            $file = $this->fileService->uploadChatAttachment($attachment, $chat, $currentUser);
            $chatHistory->setAttachment(null);
            $chatHistory->setFile($file);
            $chatHistory->setLocation(null);
            $this->em->flush();
            $this->notifyChatUsersForMessageEdited($chatHistory->getChat(), $chatHistory, $currentUser);
        }

        return $chatHistory;
    }

    /**
     * @param array $params
     * @param User $user
     * @param bool $paginated
     * @return array
     * @throws \ReflectionException
     */
    public function listChat(array $params, User $user, bool $paginated = true): array
    {
        $additionalFields = [];
        $isAllForAdmin = false;

        if (isset($params['isAllForAdmin'])) {
            $isAllForAdmin = $params['isAllForAdmin'];
            unset($params['isAllForAdmin']);
        }
        if ($user->hasPermission(Permission::CHAT_LIST_ALL) && $isAllForAdmin) {
            $params['teamIds'] = [$user->getTeamId()];
        } else {
            $params['userIds'] = [$user->getId()];
        }
        if (isset($params['isUnread']) && $params['isUnread'] == 1) {
            $chatIds = $this->em->getRepository(Chat::class)->getUserUnreadChats($user->getId());
            $params['id'] = $chatIds;
            $totalUnreadCount = $this->em->getRepository(ChatHistoryUnread::class)
                ->getUserTotalUnreadCount($user->getId());
            $additionalFields = [BaseController::ADDITIONAL_FIELDS => ['totalUnreadCount' => $totalUnreadCount]];
            unset($params['isUnread']);
        }

        $params['sort'] = isset($params['sort']) ? $params['sort'] : '-sortDate';
        $fields = $this->prepareElasticFields($params);
        $result = $this->chatFinder->find($fields, $fields['_source'] ?? [], $paginated, $user);

        return array_merge($result, $additionalFields);
    }

    /**
     * @param Chat $chat
     * @param Request $request
     * @param User $user
     * @return PaginationInterface
     */
    public function getChatHistory(Chat $chat, Request $request, User $user): PaginationInterface
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);
        $message = $request->query->get('message', null);
        $params = $request->query->all();
        $sort = isset($params['sort']) ? ltrim($params['sort'], ' -') : 'createdAt';
        $order = isset($params['sort']) && strpos($params['sort'], '-') !== 0 ? Criteria::ASC : Criteria::DESC;
        $query = $this->em->getRepository(ChatHistory::class)->getAllChatMessagesQuery($chat, $message, $sort, $order);
        $pagination = $this->paginator->paginate(
            $query,
            $page,
            ($limit == 0) ? 1 : $limit,
            ['sortFieldParameterName' => '~']
        );
        $pagination->setItems($this->formatNestedItemsToArray(
            $pagination->getItems(),
            ChatHistory::DEFAULT_DISPLAY_VALUES,
            $user
        ));

        return $pagination;
    }

    /**
     * @param Chat $chat
     * @param User $currentUser
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function sendSystemMessageChatRenamed(
        Chat $chat,
        User $currentUser
    ) {
        $chatHistory = new ChatHistory();
        $message = $this->translator->trans(
            'entities.chat.chat_has_been_renamed', ['%name%' => $chat->getName()]
        );
        $chatHistory->setChat($chat);
        $chatHistory->setUser($currentUser);
        $chatHistory->setMessage($message);
        $chatHistory->setLocation(null);
        $chatHistory->setType(ChatHistory::TYPE_SYSTEM);
        $chatHistory->setEvent(ChatHistory::EVENT_CHAT_NAME_EDITED_ID);
        $chatHistory->setEventSource($chat->getId());
        $this->em->persist($chatHistory);
        $this->notifyChatUsersForNewMessage($chat, $chatHistory, $currentUser);
        $this->em->flush();
    }

    /**
     * @param Chat $chat
     * @param User $currentUser
     * @param array $usersToAdd
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function sendSystemMessageUsersAddedToChat(
        Chat $chat,
        User $currentUser,
        array $usersToAdd
    ) {
        foreach ($usersToAdd as $userToAdd) {
            $chatHistory = new ChatHistory();
            $message = $this->translator->trans(
                'entities.chat.user_has_been_added', ['%user%' => $userToAdd->getFullName()]
            );
            $chatHistory->setChat($chat);
            $chatHistory->setUser($currentUser);
            $chatHistory->setMessage($message);
            $chatHistory->setLocation(null);
            $chatHistory->setType(ChatHistory::TYPE_SYSTEM);
            $chatHistory->setEvent(ChatHistory::EVENT_USERS_ADDED_ID);
            $chatHistory->setEventSource($userToAdd->getId());
            $this->em->persist($chatHistory);
            $this->notifyChatUsersForNewMessage($chat, $chatHistory, $currentUser);
        }

        $this->em->flush();
    }

    /**
     * @param Chat $chat
     * @param User $currentUser
     * @param array $usersToDelete
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function sendSystemMessageUsersDeletedFromChat(
        Chat $chat,
        User $currentUser,
        array $usersToDelete
    ) {
        foreach ($usersToDelete as $userToDelete) {
            $chatHistory = new ChatHistory();
            $message = $currentUser->getId() == $userToDelete->getId()
                ? $this->translator->trans(
                    'entities.chat.user_has_been_deleted', ['%user%' => $userToDelete->getFullName()]
                )
                : $this->translator->trans(
                    'entities.chat.user_was_removed_by_current_user',
                    ['%user%' => $userToDelete->getFullName(), '%currentUser%' => $currentUser->getFullName()]
                );
            $chatHistory->setChat($chat);
            $chatHistory->setUser($currentUser);
            $chatHistory->setMessage($message);
            $chatHistory->setLocation(null);
            $chatHistory->setType(ChatHistory::TYPE_SYSTEM);
            $chatHistory->setEvent(ChatHistory::EVENT_USERS_DELETED_ID);
            $chatHistory->setEventSource($userToDelete->getId());
            $this->em->persist($chatHistory);
            $this->notifyChatUsersForNewMessage($chat, $chatHistory, $currentUser);
        }

        $this->em->flush();
    }

    /**
     * @param Chat $chat
     * @param ChatHistory $chatHistory
     * @param User $currentUser
     * @return void
     */
    public function sendNotification(Chat $chat, ChatHistory $chatHistory, User $currentUser)
    {
        $recipients = $chat->getUsers();

        foreach ($recipients as $user) {
            if ($currentUser->getId() !== $user->getId()) {
                try {
                    /** @var NotificationMobileDevice $devices */
                    $device = $this->em->getRepository(NotificationMobileDevice::class)
                        ->getLastLoggedDeviceByUserBy($user->getId());

                    if ($device) {
                        $title = $chat->getName() ?: $this->getChatName($recipients->toArray());
                        $body = $chatHistory->getMessage();

                        if ($title && $body) {
                            $chatMessagesTotalUnread = $this->getChatMessagesTotalUnread($user);
                            $additionalData = [
                                'type' => Message::PUSH_TYPE_FOR_CHAT,
                                'chatId' => $chat->getId(),
                                'totalUnreadCount' => $chatMessagesTotalUnread,
                            ];
                            $this->fcmService->sendNotification(
                                $device,
                                $title,
                                $body,
                                $additionalData,
                                $chatMessagesTotalUnread
                            );
                        }
                    }
                } catch (\Throwable $e) {
                    $this->logger->error(ExceptionHelper::convertToJson($e));
                }
            }
        }
    }

    /**
     * @param Chat $chat
     * @param array $userIds
     * @param array $idsToDelete
     * @return Chat
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateChatLeader(Chat $chat, array $userIds, array $idsToDelete)
    {
        if (in_array($chat->getLeaderId(), $idsToDelete) && $userIds) {
            $chatMessageWithNewLeader = $this->em->getRepository(ChatHistory::class)
                ->getChatMessageWithNewLeader($chat->getId(), $userIds);
            $newLeader = $chatMessageWithNewLeader
                ? $this->userService->getUserById($chatMessageWithNewLeader->getEventSource())
                : null;
            $newLeader = $newLeader ?: $chat->getUsers()->first();
            $chat->setLeader($newLeader);
            $this->em->flush();
        }

        return $chat;
    }

    /**
     * @param User $user
     * @return int
     */
    public function getChatMessagesTotalUnread(User $user): int
    {
        return $this->em->getRepository(ChatHistoryUnread::class)->getUserTotalUnreadCount($user->getId());
    }
}
