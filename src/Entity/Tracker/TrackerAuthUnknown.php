<?php

namespace App\Entity\Tracker;

use App\Entity\BaseEntity;
use App\Entity\DeviceVendor;
use App\Entity\TimeZone;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * TrackerAuthUnknown
 *
 * @UniqueEntity(
 *     fields={"imei"},
 *     errorPath="imei",
 *     message="Record with this imei already exists."
 * )
 */
#[ORM\Table(name: 'tracker_auth_unknown')]
#[ORM\UniqueConstraint(columns: ['imei'])]
#[ORM\Entity(repositoryClass: 'App\Repository\Tracker\TrackerAuthUnknownRepository')]
class TrackerAuthUnknown extends BaseEntity
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'payload', type: 'string', length: 255)]
    private $payload;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'socket_id', type: 'string', length: 255, nullable: true)]
    private $socketId;

    /**
     * @var string
     */
    #[ORM\Column(name: 'imei', type: 'string', length: 255)]
    private $imei;

    /**
     * @var DeviceVendor|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\DeviceVendor', inversedBy: 'trackerAuthUnknown')]
    #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'id', nullable: true)]
    private $vendor;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private $updatedAt;

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
     * @return TrackerAuthUnknown
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
     * @return TrackerAuthUnknown
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
     * @return string
     */
    public function getImei(): string
    {
        return $this->imei;
    }

    /**
     * @param string $imei
     */
    public function setImei(string $imei): void
    {
        $this->imei = $imei;
    }

    /**
     * @return string|null
     */
    public function getSocketId(): ?string
    {
        return $this->socketId;
    }

    /**
     * @param string|null $socketId
     */
    public function setSocketId(?string $socketId): void
    {
        $this->socketId = $socketId;
    }

    /**
     * @return DeviceVendor|null
     */
    public function getVendor(): ?DeviceVendor
    {
        return $this->vendor;
    }

    /**
     * @param DeviceVendor|null $vendor
     */
    public function setVendor(?DeviceVendor $vendor): void
    {
        $this->vendor = $vendor;
    }

    /**
     * @return string
     */
    public function getTimeZoneName(): string
    {
        return TimeZone::DEFAULT_TIMEZONE['name'];
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime|null $updatedAt
     */
    public function setUpdatedAt(?\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'imei' => $this->getImei(),
            'socket' => $this->getSocketId(),
            'vendor' => $this->getVendor() ? $this->getVendor()->getName() : null,
            'createdAt' => $this->formatDate($this->getCreatedAt()),
            'updatedAt' => $this->formatDate($this->getUpdatedAt()),
        ];
    }
}
