<?php

namespace App\Entity\Tracker;

use App\Entity\BaseEntity;
use App\Entity\Device;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'tracker_payload_temp')]
#[ORM\Index(name: 'tracker_payload_temp_device_id_created_at_index', columns: ['device_id', 'created_at'])]
#[ORM\Entity(repositoryClass: 'App\Repository\Tracker\TrackerPayloadTempRepository')]
class TrackerPayloadTemp extends BaseEntity
{
    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'TrackerAuth')]
    #[ORM\JoinColumn(name: 'tracker_auth_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private ?TrackerAuth $trackerAuth;

    #[ORM\Column(name: 'payload', type: 'text')]
    private string $payload;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Device')]
    #[ORM\JoinColumn(name: 'device_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private ?Device $device;

    #[ORM\Column(name: 'is_processed', type: 'boolean', options: ['default' => '0'])]
    private bool $isProcessed = false;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
    }

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
     * Set payload.
     *
     * @param string $payload
     *
     * @return self
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;

        return $this;
    }

    /**
     * Get payload.
     *
     * @return string
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Set createdAt.
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
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return TrackerAuth|null
     */
    public function getTrackerAuth(): ?TrackerAuth
    {
        return $this->trackerAuth;
    }

    /**
     * @param TrackerAuth|null $trackerAuth
     */
    public function setTrackerAuth(?TrackerAuth $trackerAuth): void
    {
        $this->trackerAuth = $trackerAuth;
    }

    /**
     * @return Device|null
     */
    public function getDevice(): ?Device
    {
        return $this->device;
    }

    /**
     * @param Device|null $device
     */
    public function setDevice(?Device $device): void
    {
        $this->device = $device;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'device' => $this->getDevice(),
            'trackerAuth' => $this->getTrackerAuth()?->getId(),
            'createdAt' => $this->formatDate($this->getCreatedAt()),
            'isProcessed' => $this->isProcessed(),
        ];
    }

    /**
     * @return bool
     */
    public function isProcessed(): bool
    {
        return $this->isProcessed;
    }

    /**
     * @param bool $isProcessed
     */
    public function setIsProcessed(bool $isProcessed): void
    {
        $this->isProcessed = $isProcessed;
    }

    /**
     * @return string|null
     */
    public function getSocketId(): ?string
    {
        return $this->getTrackerAuth()?->getSocketId();
    }
}
