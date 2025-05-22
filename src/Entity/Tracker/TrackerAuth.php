<?php

namespace App\Entity\Tracker;

use App\Entity\Device;
use Doctrine\ORM\Mapping as ORM;

/**
 * TrackerAuth
 */
#[ORM\Table(name: 'tracker_auth')]
#[ORM\Index(name: 'tracker_auth_imei_created_at_idx', columns: ['imei', 'created_at'])]
#[ORM\Index(name: 'tracker_auth_socket_id_created_at_idx', columns: ['socket_id', 'created_at'])]
#[ORM\Index(name: 'tracker_auth_device_id_created_at_idx', columns: ['device_id', 'created_at'])]
#[ORM\Entity(repositoryClass: 'App\Repository\Tracker\TrackerAuthRepository')]
class TrackerAuth
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var Device
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Device', inversedBy: 'trackerAuth')]
    #[ORM\JoinColumn(name: 'device_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $device;

    /**
     * @var string
     */
    #[ORM\Column(name: 'payload', type: 'text')]
    private $payload;

    /**
     * @var string
     */
    #[ORM\Column(name: 'socket_id', type: 'string', length: 255)]
    private $socketId;

    /**
     * @var string
     */
    #[ORM\Column(name: 'imei', type: 'string', length: 255)]
    private $imei;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

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
     * @return TrackerAuth
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
     * Set socketId.
     *
     * @param string $socketId
     *
     * @return TrackerAuth
     */
    public function setSocketId($socketId)
    {
        $this->socketId = $socketId;

        return $this;
    }

    /**
     * Get socketId.
     *
     * @return string
     */
    public function getSocketId()
    {
        return $this->socketId;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return TrackerAuth
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
     * @return Device
     */
    public function getDevice(): Device
    {
        return $this->device;
    }

    /**
     * @param Device $device
     */
    public function setDevice(?Device $device): void
    {
        $this->device = $device;
    }
}
