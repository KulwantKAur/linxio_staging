<?php

namespace App\EventListener\Chat;

use App\Events\Chat\ChatSendNotificationEvent;
use App\Service\Chat\ChatServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ChatHistoryListener implements EventSubscriberInterface
{
    private ChatServiceInterface $chatService;

    /**
     * @param ChatServiceInterface $chatService
     */
    public function __construct(ChatServiceInterface $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ChatSendNotificationEvent::CHAT_SENT_NTF => 'onChatSendNotification',
        ];
    }

    /**
     * @param ChatSendNotificationEvent $event
     * @return void
     */
    public function onChatSendNotification(ChatSendNotificationEvent $event)
    {
        $this->chatService->sendNotification($event->getChat(), $event->getChatHistory(), $event->getUser());
    }
}
