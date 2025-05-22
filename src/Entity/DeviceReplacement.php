<?php

namespace App\Entity;

use App\Util\AttributesTrait;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'device_replacement')]
#[ORM\Entity(repositoryClass: 'App\Repository\DeviceReplacementRepository')]
class DeviceReplacement extends BaseEntity
{
    use AttributesTrait;

    public const DEFAULT_DISPLAY_VALUES = [
        'reason',
        'deviceOld',
        'deviceNew',
        'imeiOld',
        'imeiNew',
        'createdAt',
        'createdBy',
        'updatedAt',
        'updatedBy',
        'teamId',
        'client',
        'isReturned',
    ];

    public const EDITABLE_FIELDS = [
        'reason',
        'imeiOld',
        'imeiNew',
        'isReturned',
    ];


    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    /**
     * @var Device|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Device')]
    #[ORM\JoinColumn(name: 'device_old_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private ?Device $deviceOld;

    /**
     * @var Device|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Device')]
    #[ORM\JoinColumn(name: 'device_new_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private ?Device $deviceNew;

    /**
     * @var string
     */
    #[ORM\Column(name: 'imei_old', type: 'string', length: 50, nullable: false)]
    private string $imeiOld;

    /**
     * @var string
     */
    #[ORM\Column(name: 'imei_new', type: 'string', length: 50, nullable: false)]
    private string $imeiNew;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'reason', type: 'text', nullable: true)]
    private ?string $reason;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private \DateTime $createdAt;

    /**
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private ?User $createdBy;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private ?\DateTime $updatedAt;

    /**
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'updated_by', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private ?User $updatedBy;

    /**
     * @var Team
     */
    #[ORM\ManyToOne(targetEntity: 'Team')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
    private Team $team;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'is_returned', type: 'boolean', options: ['default' => '0'])]
    private bool $isReturned;

    /**
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->deviceOld = $fields['deviceOld'] ?? null;
        $this->deviceNew = $fields['deviceNew'] ?? null;
        $this->imeiOld = $fields['imeiOld'] ?? '';
        $this->imeiNew = $fields['imeiNew'] ?? '';
        $this->reason = $fields['reason'] ?? null;
        $this->createdAt = Carbon::now('UTC');
        $this->createdBy = $fields['createdBy'] ?? null;
        $this->updatedAt = $fields['updatedAt'] ?? null;
        $this->updatedBy = $fields['updatedBy'] ?? null;
        $this->isReturned = $fields['isReturned'] ?? false;
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
        if (in_array('deviceOld', $include, true)) {
            $data['deviceOld'] = $this->getDeviceOld()?->toArray(Device::SIMPLE_FIELDS);
        }
        if (in_array('deviceNew', $include, true)) {
            $data['deviceNew'] = $this->getDeviceNew()?->toArray(Device::SIMPLE_FIELDS);
        }
        if (in_array('imeiNew', $include, true)) {
            $data['imeiNew'] = $this->getImeiNew();
        }
        if (in_array('imeiOld', $include, true)) {
            $data['imeiOld'] = $this->getImeiOld();
        }
        if (in_array('imeiNew', $include, true)) {
            $data['imeiNew'] = $this->getImeiNew();
        }
        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate($this->getCreatedAt());
        }
        if (in_array('createdBy', $include, true)) {
            $data['createdBy'] = $this->getCreatedBy()?->toArray(User::CREATED_BY_FIELDS);
        }
        if (in_array('updatedAt', $include, true)) {
            $data['updatedAt'] = $this->formatDate($this->getUpdatedAt());
        }
        if (in_array('updatedBy', $include, true)) {
            $data['updatedBy'] = $this->getUpdatedBy()?->toArray(User::CREATED_BY_FIELDS);
        }
        if (in_array('reason', $include, true)) {
            $data['reason'] = $this->getReason();
        }
        if (in_array('team', $include, true)) {
            $data['team'] = $this->getTeam()->toArray();
        }
        if (in_array('teamId', $include, true)) {
            $data['teamId'] = $this->getTeamId();
        }
        if (in_array('client', $include, true)) {
            $data['client'] = $this->getClient()?->toArray(Client::SIMPLE_DISPLAY_VALUES);
        }
        if (in_array('isReturned', $include, true)) {
            $data['isReturned'] = $this->isReturned();
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
        return $this->toArray($include, $user);
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
     * @return DeviceReplacement
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return string
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
     * @return DeviceReplacement
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
     * @return DeviceReplacement
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
     * @return DeviceReplacement
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
     * @return Device|null
     */
    public function getDeviceOld(): ?Device
    {
        return $this->deviceOld;
    }

    /**
     * @param Device|null $deviceOld
     */
    public function setDeviceOld(?Device $deviceOld): void
    {
        $this->deviceOld = $deviceOld;
    }

    /**
     * @return Device|null
     */
    public function getDeviceNew(): ?Device
    {
        return $this->deviceNew;
    }

    /**
     * @param Device|null $deviceNew
     */
    public function setDeviceNew(?Device $deviceNew): void
    {
        $this->deviceNew = $deviceNew;
    }

    /**
     * @return string
     */
    public function getImeiOld(): string
    {
        return $this->imeiOld;
    }

    /**
     * @param string $imeiOld
     */
    public function setImeiOld(string $imeiOld): void
    {
        $this->imeiOld = $imeiOld;
    }

    /**
     * @return string
     */
    public function getImeiNew(): string
    {
        return $this->imeiNew;
    }

    /**
     * @param string $imeiNew
     */
    public function setImeiNew(string $imeiNew): void
    {
        $this->imeiNew = $imeiNew;
    }

    /**
     * @return string|null
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * @param string|null $reason
     */
    public function setReason(?string $reason): void
    {
        $this->reason = $reason;
    }

    /**
     * @return Team
     */
    public function getTeam(): Team
    {
        return $this->team;
    }

    /**
     * @return int
     */
    public function getTeamId(): int
    {
        return $this->getTeam()->getId();
    }

    /**
     * @param Team $team
     */
    public function setTeam(Team $team): void
    {
        $this->team = $team;
    }

    /**
     * @return Client|null
     */
    public function getClient(): ?Client
    {
        return $this->getTeam()?->getClient();
    }

    /**
     * @return bool
     */
    public function isReturned(): bool
    {
        return $this->isReturned;
    }

    /**
     * @param bool $isReturned
     */
    public function setIsReturned(bool $isReturned): void
    {
        $this->isReturned = $isReturned;
    }
}
