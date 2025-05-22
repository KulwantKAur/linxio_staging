<?php

namespace App\Entity;

use App\Util\AttributesTrait;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * ChatHistory
 */
#[ORM\Table(name: 'chat_history')]
#[ORM\Index(name: 'chat_history_user_id_created_at_index', columns: ['user_id', 'created_at'])]
#[ORM\Index(name: 'chat_history_created_at_index', columns: ['created_at'])]
#[ORM\Entity(repositoryClass: 'App\Repository\ChatHistoryRepository')]
#[ORM\EntityListeners(['App\EventListener\Chat\ChatHistoryEntityListener'])]
class ChatHistory extends BaseEntity
{
    use AttributesTrait;

    public const TYPE_DEFAULT = 1;
    public const TYPE_SYSTEM = 2;

    public const EVENT_CHAT_NAME_EDITED_ID = 1;
    public const EVENT_USERS_ADDED_ID = 2;
    public const EVENT_USERS_DELETED_ID = 3;
    public const EVENT_MESSAGE_DELETED_ID = 4;

    public const DEFAULT_DISPLAY_VALUES = [
        'user',
        'chat',
        'message',
        'createdAt',
        'updatedAt',
        'isRead',
        'isReadByOther',
        'file',
        'location',
        'type',
        'event'
    ];

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: 'User', inversedBy: 'chatHistories')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private $user;

    /**
     * @var Chat
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Chat', inversedBy: 'histories')]
    #[ORM\JoinColumn(name: 'chat_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
    private $chat;

    /**
     * @Assert\Length(
     *      min = 1,
     *      max = 255
     * )
     * @var string|null
     */
    #[ORM\Column(name: 'message', type: 'text', nullable: true)]
    private $message;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private $updatedAt;

    /**
     * @Assert\File(
     *     maxSize="10M",
     *     groups={"input"}
     * )
     * @var UploadedFile|string|null
     */
    private $attachment;

    /**
     * @var File|null
     */
    #[ORM\ManyToOne(targetEntity: 'File')]
    #[ORM\JoinColumn(name: 'file_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $file;

    /**
     * @Assert\Collection(
     *     fields = {
     *         "name" = {
     *             @Assert\Length(
     *                 min = 1,
     *                 max = 255
     *             )
     *         },
     *         "lat" = {
     *             @Assert\Regex("/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/")
     *         },
     *         "lng" = {
     *             @Assert\Regex("/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/")
     *         }
     *     }
     * )
     * @var array|null
     *
     */
    #[ORM\Column(name: 'location', type: 'json', nullable: true)]
    private $location = [
        'name' => 'address',
        'lat' => '0',
        'lng' => '0',
    ];

    /**
     * @var Collection|ChatHistoryUnread[]|null
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\ChatHistoryUnread', mappedBy: 'chatHistory', fetch: 'EXTRA_LAZY')]
    private $chatHistoryUnread;

    /**
     * @var Chat|null
     */
    private $chatWithLastChatHistory;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'type', type: 'smallint', nullable: false, options: ['default' => 1])]
    private $type = self::TYPE_DEFAULT;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'event', type: 'smallint', nullable: true)]
    private $event;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'event_source', type: 'bigint', nullable: true)]
    private $eventSource;

    /**
     * @param array $fields
     */
    public function __construct(array $fields = [])
    {
        $this->user = $fields['user'] ?? null;
        $this->message = $fields['message'] ?? null;
        $this->chat = $fields['chat'] ?? null;
        $this->createdAt = Carbon::now('UTC');
        $this->attachment = $fields['attachment'] ?? null;
        $this->file = $fields['file'] ?? null;
        $this->location = $fields['location'] ?? $this->location;
        $this->type = $fields['type'] ?? self::TYPE_DEFAULT;
        $this->event = $fields['event'] ?? null;
        $this->eventSource = $fields['eventSource'] ?? null;
        $this->chatHistoryUnread = new ArrayCollection();
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
        $data['id'] = $this->getId();

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }
        if (in_array('user', $include, true)) {
            $data['user'] = $this->getUser() ? $this->getUser()->toArray(User::SIMPLE_VALUES_CHAT) : null;
        }
        if (in_array('message', $include, true)) {
            $data['message'] = $this->getMessage();
        }
        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate($this->getCreatedAt());
        }
        if (in_array('updatedAt', $include, true)) {
            $data['updatedAt'] = $this->formatDate($this->getUpdatedAt());
        }
        if (in_array('chat', $include, true)) {
            $data['chat'] = $this->getChat() ? $this->getChat()->toArray(Chat::SIMPLE_VALUES) : null;
        }
        if (in_array('chatId', $include, true)) {
            $data['chatId'] = $this->getChatId();
        }
        if (in_array('isRead', $include, true) && $user) {
            $data['isRead'] = $this->getIsRead($user);
        }
        if (in_array('isReadByOther', $include, true)) {
            $data['isReadByOther'] = $this->getIsReadByOther();
        }
        if (in_array('file', $include, true)) {
            $data['file'] = $this->getFile() ? $this->getFile()->toArray() : null;
        }
        if (in_array('location', $include, true)) {
            $data['location'] = $this->getLocation();
        }
        if (in_array('type', $include, true)) {
            $data['type'] = $this->getType();
        }
        if (in_array('event', $include, true)) {
            $data['event'] = $this->getEvent();
        }

        return $data;
    }

    /**
     * @param array $include
     * @param User|null $user
     * @return array
     * @throws \Exception
     */
    public function toExport(array $include = [], ?User $user = null): array
    {
        $data = self::toArray($include);

        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate($this->getCreatedAt(), self::EXPORT_DATE_FORMAT);
        }
        if (in_array('updatedAt', $include, true)) {
            $data['updatedAt'] = $this->formatDate($this->getUpdatedAt(), self::EXPORT_DATE_FORMAT);
        }

        return $data;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ChatHistory
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return ChatHistory
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Get updatedAtFormatted
     *
     * @return string
     */
    public function getUpdatedAtFormatted()
    {
        return $this->updatedAt->format(self::EXPORT_DATE_FORMAT);
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     * @return self
     */
    public function setUser(?User $user): ChatHistory
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @param string|null $message
     * @return self
     */
    public function setMessage(?string $message): ChatHistory
    {
        $this->message = $message;

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
     * @return string|null
     */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * @param string|null $attachment
     * @return self
     */
    public function setAttachment($attachment)
    {
        $this->attachment = $attachment;

        return $this;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function getIsRead(User $user): bool
    {
        if ($this->getIsOwner($user)) {
            return true;
        }

        return $this->getChatHistoryUnreadByUser($user) ? false : true;
    }

    /**
     * @return bool
     */
    public function getIsReadByOther(): bool
    {
        // 1 sent 2 read
        // 1 sent 2 not read
        // 1 sent 2 read 3 not read
        // 1 sent 2 not read 3 not read
        // 1 sent 2 read 3 read
        $chatUsers = $this->getChat()->getUsers();

        foreach ($chatUsers as $chatUser) {
            if ($this->getIsOwner($chatUser) || $this->getChatHistoryUnreadByUser($chatUser)) {
                continue;
            } else {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array|null
     */
    public function getLocation(): ?array
    {
        return $this->location;
    }

    /**
     * @param array|null $location
     * @return self
     */
    public function setLocation(?array $location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @Assert\Callback()
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        if (is_null($this->attachment) && is_null($this->file) && is_null($this->message) && is_null($this->location)) {
            $context->buildViolation('You should send either attachment or message or location')->addViolation();
        }
    }

    /**
     * @return File|null
     */
    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * @param File|null $file
     * @return self
     */
    public function setFile(?File $file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return Collection|ChatHistoryUnread[]|null
     */
    public function getChatHistoryUnread(): ?Collection
    {
        return $this->chatHistoryUnread;
    }

    /**
     * @param User $user
     * @return Collection|ChatHistoryUnread[]|null
     */
    public function getAllChatHistoryUnreadByUser(User $user): ?Collection
    {
        return $this->getChatHistoryUnread()->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('user', $user))
                ->andWhere(Criteria::expr()->eq('chat', $this->getChat()))
        );
    }

    /**
     * @param User $user
     * @return ChatHistoryUnread|null
     */
    public function getChatHistoryUnreadByUser(User $user): ?ChatHistoryUnread
    {
        return $this->getAllChatHistoryUnreadByUser($user)->count()
            ? $this->getAllChatHistoryUnreadByUser($user)->first()
            : null;
    }

    /**
     * @param ChatHistoryUnread $chatHistoryUnread
     */
    public function addChatHistoryUnread(ChatHistoryUnread $chatHistoryUnread)
    {
        if (!$this->getChatHistoryUnread()->contains($chatHistoryUnread)) {
            $this->getChatHistoryUnread()->add($chatHistoryUnread);
        }
    }

    /**
     * @param ChatHistoryUnread $chatHistoryUnread
     */
    public function removeChatHistoryUnread(ChatHistoryUnread $chatHistoryUnread)
    {
        if ($this->getChatHistoryUnread()->contains($chatHistoryUnread)) {
            $this->getChatHistoryUnread()->removeElement($chatHistoryUnread);
        }
    }

    /**
     * @param User $user
     * @return bool
     */
    public function getIsOwner(User $user): bool
    {
        return $this->getUser() && ($this->getUser()->getId() == $user->getId());
    }

    /**
     * @return int|null
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @param int|null $type
     */
    public function setType(?int $type): void
    {
        $this->type = $type;
    }

    /**
     * @return int|null
     */
    public function getEvent(): ?int
    {
        return $this->event;
    }

    /**
     * @param int|null $event
     */
    public function setEvent(?int $event): void
    {
        $this->event = $event;
    }

    /**
     * @return bool
     */
    public function isSystem(): bool
    {
        return $this->getType() == self::TYPE_SYSTEM;
    }

    /**
     * @return int|null
     */
    public function getEventSource(): ?int
    {
        return $this->eventSource;
    }

    /**
     * @param int|null $eventSource
     */
    public function setEventSource(?int $eventSource): void
    {
        $this->eventSource = $eventSource;
    }

    /**
     * @return bool
     */
    public function isEmptyMessage(): bool
    {
        return !boolval($this->getMessage());
    }
}

