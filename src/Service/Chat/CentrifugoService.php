<?php

namespace App\Service\Chat;

use App\Entity\Chat;
use App\Entity\ChatHistory;
use App\Entity\User;
use App\Service\File\FileServiceInterface;
use App\Service\Firebase\FCMService;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Setting\SettingService;
use App\Service\User\UserService;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Knp\Component\Pager\PaginatorInterface;
use phpcent\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CentrifugoService extends ChatService
{
    private const TEAM_CHANNEL_PREFIX = 'team:';
    public const CENTRIFUGO_API_PATH = '/api';

    private string $channel;
    private FCMService $fcmService;

    public Client $client;

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
     * @param SettingService $settingService
     * @param FCMService $fcmService
     * @param string $centrifugoWebUrl
     * @param string $centrifugoApiKey
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
        string $centrifugoWebUrl,
        string $centrifugoApiKey
    ) {
        parent::__construct($em, $eventDispatcher, $notificationDispatcher, $logger, $chatFinder, $paginator, $translator, $validator, $userService, $fileService, $settingService, $fcmService);
        $this->client = new Client($centrifugoWebUrl . self::CENTRIFUGO_API_PATH);
        $this->client->setApiKey($centrifugoApiKey)->setTimeoutOption(7);
    }

    /**
     * @param array $data
     * @return array
     */
    private function addQueryParams(array $data): array
    {
        return ['query' => [$data]];
    }

    /**
     * @inheritDoc
     */
    public function getChatHistoryFromSource(Chat $chat): array
    {
        // @todo since
        $result = $this->history($chat->getKey(), 1000000, []);

        return $result && isset($result->result) && isset($result->result->publications)
            ? $result->result->publications
            : [];
    }

    public function history(string $channel, $limit = 0, $since = [])
    {
        return $this->client->history($channel, $limit, $since);
    }

    /**
     * @inheritDoc
     */
    public function sendMessage(Chat $chat, ChatHistory $chatHistory)
    {
        return $this->publish($chat->getKey(), $chatHistory->getMessage());
    }

    /**
     * @inheritDoc
     */
    public function publish(string $channel, array $data)
    {
        return $this->client->publish($channel, $data);
    }

    public function getChannels(string $channel)
    {
        return $this->client->channels($channel);
    }

    public function getAllChannels()
    {
        return $this->getChannels('');
    }

    /**
     * additional info for namespace in file: `devops/centrifugo/config.json`
     * @inheritDoc
     */
    public function getUniqueChatChannel(array $userIds): ?string
    {
        return 'dialogs:dialog-' . uniqid() . '#' . implode(',', $userIds);
    }

    /**
     * additional info for namespace in file: `devops/centrifugo/config.json`
     * @inheritDoc
     */
    public function getPrivateChannel(User $user): ?string
    {
        return 'private:user#' . $user->getId();
    }

    /**
     * @inheritDoc
     */
    public function getTeamChannel(User $user): ?string
    {
        return self::TEAM_CHANNEL_PREFIX . $user->getTeamId();
    }

    /**
     * @param string $channel
     * @return int
     */
    public function getChatChannelNumber(string $channel): int
    {
        $start = strpos($channel, 'dialog-');
        $end = strpos($channel, '#');

        return substr($channel, $start + 7, $end - ($start + 7));
    }

    /**
     * @param Chat $chat
     * @param User $currentUser
     * @throws \Exception
     */
    public function notifyChatUsersForNewChat(Chat $chat, User $currentUser)
    {
        $users = $chat->getUsers();

        foreach ($users as $user) {
            $channel = $this->getPrivateChannel($user);
            $this->publish($channel, ChatResponse::createFromRaw([
                'event' => self::EVENT_CHAT_CREATED,
                'user' => $currentUser,
                'data' => [
                    'chat' => $chat->toArray(array_merge(Chat::SIMPLE_VALUES, ['users']), $user),
                ],
            ]));
        }
    }

    /**
     * @param Chat $chat
     * @param User $currentUser
     * @throws \Exception
     */
    public function notifyChatUsersForChatEdited(Chat $chat, User $currentUser)
    {
        $users = $chat->getUsers();

        foreach ($users as $user) {
            $channel = $this->getPrivateChannel($user);
            $this->publish($channel, ChatResponse::createFromRaw([
                'event' => self::EVENT_CHAT_EDITED,
                'user' => $currentUser,
                'data' => [
                    'chat' => $chat->toArray(array_merge(Chat::SIMPLE_VALUES, ['users']), $user),
                ],
            ]));
        }
    }

    /**
     * @param Chat $chat
     * @param User $currentUser
     * @throws \Exception
     */
    public function notifyChatUsersForChatDeleted(Chat $chat, User $currentUser)
    {
        $users = $chat->getUsers();

        foreach ($users as $user) {
            $channel = $this->getPrivateChannel($user);
            $this->publish($channel, ChatResponse::createFromRaw([
                'event' => self::EVENT_CHAT_DELETED,
                'user' => $currentUser,
                'data' => [
                    'chat' => $chat->toArray(array_merge(Chat::SIMPLE_VALUES, ['users']), $user),
                ],
            ]));
        }
    }

    /**
     * @param Chat $chat
     * @param User $currentUser
     * @param array $usersToNotify
     * @param array $usersToDelete
     * @throws \Exception
     */
    public function notifyUsersDeletedFromChat(
        Chat $chat,
        User $currentUser,
        array $usersToNotify,
        array $usersToDelete
    ) {
        if (!empty($usersToDelete)) {
            foreach ($usersToNotify as $user) {
                $channel = $this->getPrivateChannel($user);
                $this->publish($channel, ChatResponse::createFromRaw([
                    'event' => self::EVENT_USERS_DELETED_FROM_CHAT,
                    'user' => $currentUser,
                    'data' => [
                        'chat' => $chat->toArray(array_merge(Chat::SIMPLE_VALUES, ['users']), $user),
                        'users' => array_map(function (User $user) {
                            return $user->toArray(User::SIMPLE_VALUES_CHAT);
                        }, $usersToDelete),
                    ],
                ]));
            }
        }
    }

    /**
     * @param Chat $chat
     * @param User $currentUser
     * @param array $usersToNotify
     * @param array $usersToAdd
     * @throws \Exception
     */
    public function notifyUsersAddedToChat(Chat $chat, User $currentUser, array $usersToNotify, array $usersToAdd)
    {
        if (!empty($usersToAdd)) {
            foreach ($usersToNotify as $user) {
                $channel = $this->getPrivateChannel($user);
                $this->publish($channel, ChatResponse::createFromRaw([
                    'event' => self::EVENT_USERS_ADDED_TO_CHAT,
                    'user' => $currentUser,
                    'data' => [
                        'chat' => $chat->toArray(array_merge(Chat::SIMPLE_VALUES, ['users']), $user),
                        'users' => array_map(function (User $user) {
                            return $user->toArray(User::SIMPLE_VALUES_CHAT);
                        }, $usersToAdd),
                    ],
                ]));
            }
        }
    }

    /**
     * @param Chat $chat
     * @param ChatHistory $chatHistory
     * @param User $currentUser
     * @throws \Exception
     */
    public function notifyChatUsersForNewMessage(Chat $chat, ChatHistory $chatHistory, User $currentUser)
    {
        $users = $chat->getUsers();

        foreach ($users as $user) {
            $channel = $this->getPrivateChannel($user);
            $this->publish($channel, ChatResponse::createFromRaw([
                'event' => self::EVENT_MESSAGE_SENT,
                'user' => $currentUser,
                'data' => [
                    'chat' => $chat->toArray([], $user),
                    'message' => $chatHistory->toArray([], $user),
                ],
            ]));
        }
    }

    /**
     * @param Chat $chat
     * @param ChatHistory $chatHistory
     * @param User|null $currentUser
     * @throws \Exception
     */
    public function notifyChatUsersForMessageEdited(Chat $chat, ChatHistory $chatHistory, ?User $currentUser)
    {
        $users = $chat->getUsers();

        foreach ($users as $user) {
            $channel = $this->getPrivateChannel($user);
            $this->publish($channel, ChatResponse::createFromRaw([
                'event' => self::EVENT_MESSAGE_EDITED,
                'user' => $currentUser,
                'data' => [
                    'chat' => $chat->toArray([], $user),
                    'message' => $chatHistory->toArray([], $user),
                ],
            ]));
        }
    }

    /**
     * @param Chat $chat
     * @param ChatHistory $chatHistory
     * @param User|null $currentUser
     * @throws \Exception
     */
    public function notifyChatUsersForMessageRead(Chat $chat, ChatHistory $chatHistory, ?User $currentUser)
    {
        $users = $chat->getUsers();

        foreach ($users as $user) {
            $channel = $this->getPrivateChannel($user);
            $this->publish($channel, ChatResponse::createFromRaw([
                'event' => self::EVENT_MESSAGE_READ,
                'user' => $currentUser,
                'data' => [
                    'chat' => $chat->toArray(['id'], $user),
                    'message' => $chatHistory->toArray(['id'], $user),
                ],
            ]));
        }
    }

    /**
     * @param Chat $chat
     * @param ChatHistory $chatHistory
     * @param User $currentUser
     * @throws \Exception
     */
    public function notifyChatUsersForMessageDeleted(Chat $chat, ChatHistory $chatHistory, User $currentUser)
    {
        $users = $chat->getUsers();

        foreach ($users as $user) {
            $channel = $this->getPrivateChannel($user);
            $this->publish($channel, ChatResponse::createFromRaw([
                'event' => self::EVENT_MESSAGE_DELETED,
                'user' => $currentUser,
                'data' => [
                    'chat' => $chat->toArray([], $user),
                    'message' => $chatHistory->toArray([], $user),
                ],
            ]));
        }
    }

    /**
     * @param User $currentUser
     * @throws \Exception
     */
    public function notifyTeamUserStatusUpdated(User $currentUser)
    {
        $channel = $this->getTeamChannel($currentUser);
        $this->publish($channel, ChatResponse::createFromRaw([
            'event' => self::EVENT_USER_STATUS_UPDATED,
            'user' => $currentUser,
            'data' => null,
        ]));
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function handleCentrifugoSubscribeEvent(array $params)
    {
        $user = isset($params['user']) ? $this->userService->getUserById($params['user']) : null;
        $channel = $params['channel'] ?? null;
        $isTeamSubscribe = $channel
            ? substr($channel, 0, strlen(self::TEAM_CHANNEL_PREFIX)) == self::TEAM_CHANNEL_PREFIX
            : false;

        if ($user && $channel && $isTeamSubscribe) {
            $this->notifyTeamUserStatusUpdated($channel, $user);
        }
    }
}
