<?php

namespace App\EventListener\Chat;

use App\Entity\Chat;
use App\Entity\ChatHistory;
use App\Entity\ChatHistoryUnread;
use App\Service\Chat\ChatService;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;

class ChatHistoryEntityListener
{
    private $em;
    private $logger;

    public function __construct(
        EntityManager $em,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * @param ChatHistory $chatHistory
     * @throws \Exception
     */
    public function postPersist(ChatHistory $chatHistory)
    {
        try {
            if ($chatHistory->isSystem()) {
                return;
            }

            $chat = $chatHistory->getChat();
            $users = $chat->getUsers();

            foreach ($users as $user) {
                if ($chatHistory->getUser() && $chatHistory->getUser()->getId() == $user->getId()) {
                    continue;
                }

                $chatHistoryUser = new ChatHistoryUnread();
                $chatHistoryUser->setChat($chat);
                $chatHistoryUser->setUser($user);
                $chatHistoryUser->setChatHistory($chatHistory);
                $this->em->persist($chatHistoryUser);
                $chatHistory->addChatHistoryUnread($chatHistoryUser);
            }

            $this->em->flush();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['name' => ChatService::class]);
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param ChatHistory $chatHistory
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function preRemove(ChatHistory $chatHistory)
    {
        $chatHistory->getChatHistoryUnread()->forAll(function($key, ChatHistoryUnread $chatHistoryUnread) {
            $this->em->remove($chatHistoryUnread);
            return true;
        });
        $chat = $this->em->getRepository(Chat::class)->getChatByLastChatHistory($chatHistory);

        if ($chat) {
            $chat->setLastChatHistory($chat->getPreviousChatHistory($chatHistory));
        }

        $this->em->flush();
    }
}