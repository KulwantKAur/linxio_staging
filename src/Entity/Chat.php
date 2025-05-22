<?php

namespace App\Entity;

use App\Util\AttributesTrait;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Chat
 *
 * @UniqueEntity(
 *     fields={"channel"},
 *     message="Chat with such channel already exists."
 * )
 */
#[ORM\Table(name: 'chat')]
#[ORM\Index(name: 'chat_channel_created_at_index', columns: ['channel', 'created_at'])]
#[ORM\Entity(repositoryClass: 'App\Repository\ChatRepository')]
class Chat extends BaseEntity
{
    use AttributesTrait;

    public const NAME_MAX_LENGTH = 255;
    public const SCOPE_INDIVIDUAL = 'individual';
    public const SCOPE_GROUP = 'group';
    public const DEFAULT_DISPLAY_VALUES = [
        'id',
        'name',
        'channel',
        'createdAt',
        'createdBy',
        'updatedAt',
        'updatedBy',
        'lastSentAt',
        'lastChatHistory',
        'users',
        'unreadCount',
        'leader',
        'isIndividual',
    ];
    public const SIMPLE_VALUES = [
        'id',
        'name',
        'channel',
        'lastSentAt',
    ];

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $createdBy;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private $updatedAt;

    /**
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'updated_by', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $updatedBy;

    /**
     * @Assert\Length(
     *      min = 1,
     *      max = 255
     * )
     *
     * @var string|null
     */
    #[ORM\Column(name: 'name', type: 'string', nullable: true)]
    private $name;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'channel', type: 'string', nullable: true, unique: true)]
    private $channel;

    /**
     * @var ChatHistory|null
     */
    #[ORM\OneToOne(targetEntity: 'App\Entity\ChatHistory')]
    #[ORM\JoinColumn(name: 'last_chat_history_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $lastChatHistory;

    /**
     * @var ChatHistory[]|Collection
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\ChatHistory', mappedBy: 'chat', fetch: 'EXTRA_LAZY')]
    private $histories;

    /**
     * @var ChatHistoryUnread[]|Collection
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\ChatHistoryUnread', mappedBy: 'chat', fetch: 'EXTRA_LAZY')]
    private $unreadHistories;

    /**
     * @Assert\Count(
     *     min = "1",
     *     minMessage = "You have to select at least 1 user"
     * )
     *
     * @var User[]|Collection
     */
    #[ORM\JoinTable(name: 'chat_users')]
    #[ORM\ManyToMany(targetEntity: 'User', inversedBy: 'chats', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    private $users;

    /**
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'leader', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private ?User $leader;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'is_individual', type: 'boolean', options: ['default' => 'false'])]
    private bool $isIndividual = false;

    /**
     * @param array $fields
     */
    public function __construct(array $fields = [])
    {
        $this->createdAt = Carbon::now('UTC');
        $this->createdBy = $fields['createdBy'] ?? null;
        $this->name = $fields['name'] ?? null;
        $this->channel = $fields['channel'] ?? null;
        $this->users = new ArrayCollection();
        $this->histories = new ArrayCollection();
        $this->unreadHistories = new ArrayCollection();
        $this->leader = $fields['leader'] ?? null;
        $this->isIndividual = $fields['isIndividual'] ?? false;
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
        if (in_array('name', $include, true)) {
            $data['name'] = $this->getName();
        }
        if (in_array('channel', $include, true)) {
            $data['channel'] = $this->getChannel();
        }
        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate($this->getCreatedAt());
        }
        if (in_array('createdBy', $include, true)) {
            $data['createdBy'] = $this->getCreatedBy()?->toArray(User::SIMPLE_VALUES_CHAT);
        }
        if (in_array('updatedAt', $include, true)) {
            $data['updatedAt'] = $this->formatDate($this->getUpdatedAt());
        }
        if (in_array('updatedBy', $include, true)) {
            $data['updatedBy'] = $this->getUpdatedBy()?->toArray(User::SIMPLE_VALUES_CHAT);
        }
        if (in_array('users', $include, true)) {
            $data['users'] = $this->getUsersArray();
        }
        if (in_array('lastChatHistory', $include, true)) {
            $data['lastChatHistory'] = $this->getLastChatHistory()
                ? $this->getLastChatHistory()->toArray([
                    'id',
                    'message',
                    'createdAt',
                    'createdBy',
                    'isRead',
                    'file',
                    'location',
                    'user'
                ], $user)
                : null;
        }
        if (in_array('lastSentAt', $include, true)) {
            $data['lastSentAt'] = $this->getLastChatHistory()
                ? $this->formatDate($this->getLastChatHistory()->getCreatedAt())
                : null;
        }
        if (in_array('unreadCount', $include, true) && $user) {
            $data['unreadCount'] = $this->getUnreadCount($user);
        }
        if (in_array('leader', $include, true)) {
            $data['leader'] = $this->getLeader()?->toArray(User::SIMPLE_VALUES_CHAT);
        }
        if (in_array('isIndividual', $include, true)) {
            $data['isIndividual'] = $this->isIndividual();
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set createdBy
     *
     * @param User $createdBy
     *
     * @return self
     */
    public function setCreatedBy(User $createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return self
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
     * Set updatedBy
     *
     * @param User $updatedBy
     *
     * @return self
     */
    public function setUpdatedBy(User $updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return User|null
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getUsersArray()
    {
        return array_map(
            function (User $user) {
                $userData = $user->toArray(User::SIMPLE_VALUES_CHAT);
                $userData['leader'] = $this->getLeader() && $user->getId() == $this->getLeaderId();

                return $userData;
            },
            $this->getUsers()->getValues()
        );
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @return array
     */
    public function getUsersEntityArray()
    {
        return $this->getUsers()->getValues();
    }

    /**
     * @return array
     */
    public function getUserIds(): array
    {
        return $this->getUsers()->map(
            function (User $user) {
                return $user->getId();
            }
        )->getValues();
    }

    /**
     * @param User $user
     */
    public function addUser(User $user)
    {
        if (!$this->getUsers()->contains($user)) {
            $this->getUsers()->add($user);
        }
    }

    /**
     * @param User $user
     */
    public function removeUser(User $user)
    {
        $this->getUsers()->removeElement($user);
    }

    /**
     * @param User $user
     * @return mixed
     */
    public function hasUser(User $user)
    {
        return boolval($this->getUsers()->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('id', $user->getId()))
        )->first());
    }

    /**
     *
     */
    public function removeAllUsers()
    {
        $this->getUsers()->clear();
    }

    /**
     * @return int
     */
    public function getUsersCount(): int
    {
        return $this->getUsers()->count();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name ? substr($name, 0, 255) : null;

        return $this;
    }

    public function getChannel(): ?string
    {
        return $this->channel;
    }

    public function setChannel(?string $channel): self
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @return ChatHistory[]|Collection
     */
    public function getHistories()
    {
        return $this->histories;
    }

    /**
     * @param ChatHistory $chatHistory
     * @return ChatHistory|null
     */
    public function getPreviousChatHistory(ChatHistory $chatHistory): ?ChatHistory
    {
        $previousChatHistory = $this->getHistories()->matching(
            Criteria::create()
                ->where(Criteria::expr()->lte('createdAt', $chatHistory->getCreatedAt()))
                ->andWhere(Criteria::expr()->neq('id', $chatHistory->getId()))
                ->orderBy(['createdAt' => Criteria::DESC])
        )->first();

        return $previousChatHistory ?: null;
    }

    /**
     * @return ChatHistoryUnread[]|Collection
     */
    public function getUnreadHistories()
    {
        return $this->unreadHistories;
    }

    /**
     * @param ChatHistoryUnread[]|Collection $unreadHistories
     * @return Chat
     */
    public function setUnreadHistories($unreadHistories)
    {
        $this->unreadHistories = $unreadHistories;

        return $this;
    }

    /**
     * @param User $user
     * @return Collection|null
     */
    public function getUnreadHistoriesByUser(User $user): ?Collection
    {
        return $this->getUnreadHistories()->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('user', $user))
        );
    }

    /**
     * @param User $user
     * @param ChatHistory $chatHistory
     * @return Collection|null
     */
    public function getUnreadHistoriesByUserAndChatHistory(User $user, ChatHistory $chatHistory): ?Collection
    {
        return $this->getUnreadHistoriesByUser($user)->matching(
            Criteria::create()
                ->where(Criteria::expr()->lte('createdAt', $chatHistory->getCreatedAt()))
        );
    }

    /**
     * @param User $user
     * @param ChatHistoryUnread $chatHistoryUnread
     * @return Collection|null
     */
    public function getUnreadHistoriesByUserAndChatHistoryUnread(
        User $user,
        ChatHistoryUnread $chatHistoryUnread
    ): ?Collection {
        return $this->getUnreadHistoriesByUser($user)->matching(
            Criteria::create()
                ->where(Criteria::expr()->lte('createdAt', $chatHistoryUnread->getCreatedAt()))
        );
    }

    /**
     * @param User $user
     * @param ChatHistoryUnread $unreadHistoryByUser
     */
    public function removeUserUnreadMessage(User $user, ChatHistoryUnread $unreadHistoryByUser)
    {
        $unreadHistoriesByUser = $this->getUnreadHistoriesByUser($user);

        if ($unreadHistoriesByUser->contains($unreadHistoryByUser)) {
            $unreadHistoriesByUser->removeElement($unreadHistoryByUser);
        }
    }

    /**
     * @return \DateTimeInterface
     */
    public function getSortDate(): \DateTimeInterface
    {
        return $this->getLastChatHistory() ? $this->getLastChatHistory()->getCreatedAt() : $this->getCreatedAt();
    }

    /**
     * @return ChatHistory|null
     */
    public function getLastChatHistory(): ?ChatHistory
    {
        return $this->lastChatHistory;
    }

    /**
     * @param ChatHistory|null $lastChatHistory
     * @return self
     */
    public function setLastChatHistory(?ChatHistory $lastChatHistory)
    {
        $this->lastChatHistory = $lastChatHistory;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getLastSentAt(): ?\DateTimeInterface
    {
        return $this->getLastChatHistory() ? $this->getLastChatHistory()->getCreatedAt() : null;
    }

    /**
     * @param User $user
     * @return int
     */
    public function getUnreadCount(User $user): int
    {
        return $this->getUnreadHistoriesByUser($user)->count();
    }

    /**
     * @return string
     */
    public function getScope(): string
    {
        return $this->isIndividual() ? self::SCOPE_INDIVIDUAL : self::SCOPE_GROUP;
    }

    public function getTeam(): ?Team
    {
        return $this->getCreatedBy()?->getTeam();
    }

    /**
     * @return User|null
     */
    public function getLeader(): ?User
    {
        return $this->leader;
    }

    /**
     * @return int|null
     */
    public function getLeaderId(): ?int
    {
        return $this->getLeader()?->getId();
    }

    /**
     * @param User|null $leader
     */
    public function setLeader(?User $leader): void
    {
        $this->leader = $leader;
    }

    /**
     * @return bool
     */
    public function isIndividual(): bool
    {
        return $this->isIndividual;
    }

    /**
     * @param bool $isIndividual
     */
    public function setIsIndividual(bool $isIndividual): void
    {
        $this->isIndividual = $isIndividual;
    }
}

