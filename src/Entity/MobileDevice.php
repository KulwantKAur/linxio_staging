<?php

namespace App\Entity;

use App\Util\AttributesTrait;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;

/**
 * MobileDevice
 */
#[ORM\Table(name: 'mobile_device')]
#[ORM\Entity(repositoryClass: 'App\Repository\MobileDeviceRepository')]
class MobileDevice extends BaseEntity
{
    use AttributesTrait;

    /**
     * MobileDevice constructor.
     * @param array $fields
     * @throws \Exception
     */
    public function __construct(array $fields)
    {
        $this->operationSystem = $fields['operationSystem'] ?? null;
        $this->deviceId = $fields['deviceId'] ?? null;
        $this->team = $fields['team'] ?? null;
        $this->createdAt = new \DateTime();
        $this->loginWithId = $fields['loginWithId'] ?? false;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function toArray(): array
    {
        return [
            'operationSystem' => $this->getOperationSystem(),
            'deviceId' => $this->getDeviceId(),
            'team' => $this->getTeam()->toArray(Team::DEFAULT_DISPLAY_VALUES),
            'createdAt' => $this->formatDate($this->getCreatedAt()),
            'updatedAt' => $this->formatDate($this->getUpdatedAt()),
            'lastLoggedAt' => $this->formatDate($this->getLastLoggedAt()),
            'loginWithId' => $this->getLoginWithId()
        ];
    }

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'operation_system', type: 'string', length: 255, nullable: true)]
    private $operationSystem;

    /**
     * @var string
     */
    #[ORM\Column(name: 'device_id', type: 'string', length: 255, unique: true)]
    private $deviceId;

    /**
     * @var Team|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Team')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id', nullable: true)]
    private $team;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'last_logged_at', type: 'datetime', nullable: true)]
    private $lastLoggedAt;

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
     * @var bool
     */
    #[ORM\Column(name: 'login_with_id', type: 'boolean', nullable: false, options: ['default' => false])]
    private $loginWithId = false;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set operationSystem.
     *
     * @param string|null $operationSystem
     *
     * @return MobileDevice
     */
    public function setOperationSystem($operationSystem = null)
    {
        $this->operationSystem = $operationSystem;

        return $this;
    }

    /**
     * Get operationSystem.
     *
     * @return string|null
     */
    public function getOperationSystem()
    {
        return $this->operationSystem;
    }

    /**
     * Set deviceId.
     *
     * @param string $deviceId
     *
     * @return MobileDevice
     */
    public function setDeviceId($deviceId)
    {
        $this->deviceId = $deviceId;

        return $this;
    }

    /**
     * Get deviceId.
     *
     * @return string
     */
    public function getDeviceId()
    {
        return $this->deviceId;
    }

    /**
     * Set team.
     *
     * @param Team $team
     *
     * @return MobileDevice
     */
    public function setTeam(Team $team)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * Get team.
     *
     * @return Team
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return MobileDevice
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return MobileDevice
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }


    /**
     * @return Carbon|\DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set lastLoggedAt
     *
     * @param \DateTime $lastLoggedAt
     *
     * @return MobileDevice
     */
    public function setLastLoggedAt($lastLoggedAt)
    {
        $this->lastLoggedAt = $lastLoggedAt;

        return $this;
    }

    /**
     * Get lastLoggedAt
     *
     * @return \DateTime
     */
    public function getLastLoggedAt()
    {
        return $this->lastLoggedAt;
    }


    /**
     * @param bool $value
     * @return $this
     */
    public function setLoginWithId(bool $value)
    {
        $this->loginWithId = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function getLoginWithId()
    {
        return $this->loginWithId;
    }
}
