<?php

namespace App\Entity\Notification;

use App\Entity\BaseEntity;
use App\Entity\User;
use App\Util\AttributesTrait;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;

/**
 * NotificationMobileDevice
 */
#[ORM\Table(name: 'notification_mobile_device')]
#[ORM\Entity(repositoryClass: 'App\Repository\Notification\NotificationMobileDeviceRepository')]
class NotificationMobileDevice extends BaseEntity
{
    use AttributesTrait;

    public const IOS = 'ios';
    public const ANDROID = 'android';

    /**
     * NotificationMobileDevice constructor.
     * @param array $fields
     * @throws \Exception
     */
    public function __construct(array $fields)
    {
        $this->setOperationSystem($fields['operationSystem'] ?? null);
        $this->setDeviceToken($fields['deviceToken'] ?? null);
        $this->setCreatedAt(new \DateTime());
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function toArray(): array
    {
        return [
            'operationSystem' => $this->getOperationSystem(),
            'deviceToken' => $this->getDeviceToken(),
            'user' => $this->getUser()->toArray(User::DISPLAYED_VALUES),
            'createdAt' => $this->formatDate($this->getCreatedAt()),
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
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\User')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $user;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'device_token', type: 'string', length: 255, nullable: true)]
    private $deviceToken;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'operation_system', type: 'string', length: 255, nullable: true)]
    private $operationSystem;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'last_logged_at', type: 'datetime', nullable: true)]
    private $lastLoggedAt;


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
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     */
    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return string|null
     */
    public function getDeviceToken(): ?string
    {
        return $this->deviceToken;
    }

    /**
     * @param string|null $deviceToken
     */
    public function setDeviceToken(?string $deviceToken)
    {
        $this->deviceToken = $deviceToken;
    }

    /**
     * Set operationSystem.
     *
     * @param string|null $operationSystem
     *
     * @return $this
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return $this
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
     * @return bool
     */
    public function isIos()
    {
        return $this->getOperationSystem() === self::IOS;
    }

    /**
     * @return bool
     */
    public function isAndroid()
    {
        return $this->getOperationSystem() === self::ANDROID;
    }

    /**
     * @return \DateTime
     *
     * @return $this
     */
    public function getLastLoggedAt(): \DateTime
    {
        return $this->lastLoggedAt;
    }

    /**
     * @param \DateTime $lastLoggedAt
     *
     * @return $this
     */
    public function setLastLoggedAt(\DateTime $lastLoggedAt)
    {
        $this->lastLoggedAt = $lastLoggedAt;
    }
}
