<?php

namespace App\Entity\Tracker\Teltonika;

use App\Entity\DeviceModel;
use Doctrine\ORM\Mapping as ORM;

/**
 * TrackerSensorEvent
 */
#[ORM\Table(name: 'tracker_sensor_event')]
#[ORM\UniqueConstraint(name: 'tracker_sensor_event_device_model_id_remote_id_uindex', columns: ['device_model_id', 'remote_id'])]
#[ORM\Entity(repositoryClass: 'App\Repository\Tracker\Teltonika\TrackerSensorEventRepository')]
class TrackerSensorEvent
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var int
     */
    #[ORM\Column(name: 'remote_id', type: 'integer')]
    private $remoteId;

    /**
     * @var string
     */
    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    private $name;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'label', type: 'string', length: 255, nullable: true)]
    private $label;

    /**
     * @var DeviceModel|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\DeviceModel')]
    #[ORM\JoinColumn(name: 'device_model_id', referencedColumnName: 'id', nullable: true)]
    private $deviceModel;


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
     * Set remoteId.
     *
     * @param int $remoteId
     *
     * @return TrackerSensorEvent
     */
    public function setRemoteId($remoteId)
    {
        $this->remoteId = $remoteId;

        return $this;
    }

    /**
     * Get remoteId.
     *
     * @return int
     */
    public function getRemoteId()
    {
        return $this->remoteId;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return TrackerSensorEvent
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set label.
     *
     * @param string|null $label
     *
     * @return TrackerSensorEvent
     */
    public function setLabel($label = null)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label.
     *
     * @return string|null
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return DeviceModel|null
     */
    public function getDeviceModel(): ?DeviceModel
    {
        return $this->deviceModel;
    }

    /**
     * @param DeviceModel|null $deviceModel
     */
    public function setDeviceModel(?DeviceModel $deviceModel): void
    {
        $this->deviceModel = $deviceModel;
    }
}
