<?php

namespace App\Entity;

use App\Util\AttributesTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * ChatHistoryUnread
 */
#[ORM\Table(name: 'chat_history_unread')]
#[ORM\Index(name: 'chat_history_unread_chat_id_user_id_index', columns: ['chat_id', 'user_id'])]
#[ORM\Entity(repositoryClass: 'App\Repository\ChatHistoryUnreadRepository')]
class ChatHistoryUnread extends BaseEntity
{
    use AttributesTrait;

    public const DEFAULT_DISPLAY_VALUES = [
        'user',
        'chat',
        'chatHistory',
        'createdAt',
    ];

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'User', inversedBy: 'chatHistoriesUnread')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
    private $user;

    /**
     * @var Chat
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Chat', inversedBy: 'unreadHistories')]
    #[ORM\JoinColumn(name: 'chat_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
    private $chat;

    /**
     * @var ChatHistory
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\ChatHistory', inversedBy: 'chatHistoryUnread')]
    #[ORM\JoinColumn(name: 'chat_history_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
    private $chatHistory;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: false)]
    private $createdAt;

    /**
     * @param array $fields
     */
    public function __construct(array $fields = [])
    {
        $this->user = $fields['user'] ?? null;
        $this->chatHistory = $fields['chatHistory'] ?? null;
        $this->chat = $fields['chat'] ?? null;
        $this->createdAt = $fields['createdAt'] ?? new \DateTime();
    }

    /**
     * @param array $include
     * @param User|null $user
     * @return array
     * @throws \Exception
     */
    public function toArray(array $include = [], ?User $user = null): array
    {
        $data = [];
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }
        if (in_array('user', $include, true)) {
            $data['user'] = $this->getUser()->toArray(User::SIMPLE_VALUES_CHAT);
        }
        if (in_array('chat', $include, true)) {
            $data['chat'] = $this->getChat()->toArray(Chat::SIMPLE_VALUES);
        }
        if (in_array('chatId', $include, true)) {
            $data['chatId'] = $this->getChat()->getId();
        }
        if (in_array('chatHistory', $include, true)) {
            $data['chatHistory'] = $this->getChatHistory()->toArray();
        }
        if (in_array('chatHistoryId', $include, true)) {
            $data['chatHistoryId'] = $this->getChatHistory()?->getId();
        }
        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate($this->getCreatedAt());
        }

        return $data;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return self
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return ChatHistory|null
     */
    public function getChatHistory(): ?ChatHistory
    {
        return $this->chatHistory;
    }

    /**
     * @param ChatHistory|null $chatHistory
     * @return self
     */
    public function setChatHistory(?ChatHistory $chatHistory): self
    {
        $this->chatHistory = $chatHistory;

        return $this;
    }

    /**
     * @return Chat
     */
    public function getChat(): Chat
    {
        return $this->chat;
    }

    /**
     * @param Chat $chat
     * @return self
     */
    public function setChat(Chat $chat)
    {
        $this->chat = $chat;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getChatId(): ?int
    {
        return $this->getChat() ? $this->getChat()->getId() : null;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return ChatHistoryUnread
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}

