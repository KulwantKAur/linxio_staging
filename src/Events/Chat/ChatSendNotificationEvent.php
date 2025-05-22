<?php

declare(strict_types=1);

namespace App\Events\Chat;

use App\Entity\Chat;
use App\Entity\ChatHistory;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class ChatSendNotificationEvent extends Event
{
    public const CHAT_SENT_NTF = 'app.event.chat.sendNotification';

    protected Chat $chat;
    protected ChatHistory $chatHistory;
    protected User $user;

    /**
     * @param Chat $chat
     * @param ChatHistory $chatHistory
     */
    public function __construct(Chat $chat, ChatHistory $chatHistory, User $user)
    {
        $this->chat = $chat;
        $this->chatHistory = $chatHistory;
        $this->user = $user;
    }

    /**
     * @return Chat
     */
    public function getChat(): Chat
    {
        return $this->chat;
    }

    /**
     * @return ChatHistory
     */
    public function getChatHistory(): ChatHistory
    {
        return $this->chatHistory;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
