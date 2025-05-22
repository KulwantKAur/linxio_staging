<?php

namespace App\Entity;

use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;

/**
 * Note
 */
#[ORM\Table(name: 'note')]
#[ORM\Entity(repositoryClass: 'App\Repository\NoteRepository')]
class Note extends BaseEntity
{
    public const ALLOWED_TYPES = [
        self::TYPE_ADMIN,
        self::TYPE_CLIENT,
        self::TYPE_RESELLER,
    ];

    public const TYPE_ADMIN = 'admin';
    public const TYPE_CLIENT = 'client';
    public const TYPE_RESELLER = 'reseller';


    /**
     * Note constructor.
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->client = $fields['client'] ?? null;
        $this->note = $fields['note'];
        $this->noteType = $fields['noteType'];
        $this->device = $fields['device'] ?? null;
        $this->vehicle = $fields['vehicle'] ?? null;
        $this->reseller = $fields['reseller'] ?? null;
        $this->createdBy = $fields['createdBy'];
        $this->createdAt = Carbon::now('UTC');
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'note' => $this->note,
            'noteType' => $this->noteType,
            'device' => $this->getDeviceData(),
            'vehicle' => $this->getVehicleData(),
            'createdAt' => $this->formatDate($this->createdAt),
            'createdBy' => $this->createdBy ? [
                'id' => $this->getCreatedBy()->getId(),
                'fullName' => $this->getCreatedBy()->getFullName(),
                'email' => $this->getCreatedBy()->getEmail(),
            ] : [],
        ];
    }

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var int
     */
    #[ORM\ManyToOne(targetEntity: 'Client')]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private $client;

    /**
     * @var string
     */
    #[ORM\Column(name: 'note', type: 'text')]
    private $note;

    /**
     * @var string
     */
    #[ORM\Column(name: 'note_type', type: 'string', length: 30)]
    private $noteType;

    /**
     * @var int
     */
    #[ORM\ManyToOne(targetEntity: 'Device', inversedBy: 'notes')]
    #[ORM\JoinColumn(name: 'device_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private $device;

    /**
     * @var Reseller
     */
    #[ORM\ManyToOne(targetEntity: 'Reseller', inversedBy: 'notes')]
    #[ORM\JoinColumn(name: 'reseller_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private $reseller;

    /**
     * @var int
     */
    #[ORM\ManyToOne(targetEntity: 'Vehicle', inversedBy: 'notes')]
    #[ORM\JoinColumn(name: 'vehicle_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private $vehicle;


    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * @var int
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $createdBy;


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
     * Set client
     *
     * @param Client $client
     *
     * @return Note
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set device
     *
     * @param Device $device
     *
     * @return Note
     */
    public function setDevice($device)
    {
        $this->device = $device;

        return $this;
    }

    /**
     * Get device
     *
     * @return Device
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * Get vehicle
     *
     * @return Vehicle
     */
    public function getVehicle()
    {
        return $this->vehicle;
    }

    /**
     * Set device
     *
     * @param Vehicle $vehicle
     *
     * @return Note
     */
    public function setVehicle($vehicle)
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    /**
     * Set note
     *
     * @param string $note
     *
     * @return Note
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set noteType
     *
     * @param string $noteType
     *
     * @return Note
     */
    public function setNoteType($noteType)
    {
        $this->noteType = $noteType;

        return $this;
    }

    /**
     * Get noteType
     *
     * @return string
     */
    public function getNoteType()
    {
        return $this->noteType;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Note
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
     * @return Note
     */
    public function setCreatedBy($createdBy)
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
     * @return array|null
     * @throws \Exception
     */
    public function getDeviceData()
    {
        return $this->getDevice() ? $this->getDevice()->toArray(Device::SIMPLE_FIELDS) : null;
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public function getVehicleData()
    {
        return $this->getVehicle() ? $this->getVehicle()->toArray(Vehicle::DEFAULT_DISPLAY_VALUES) : null;
    }

    /**
     * Get reseller
     *
     * @return Reseller
     */
    public function getReseller()
    {
        return $this->reseller;
    }

    /**
     * Set reseller
     *
     * @param Reseller $reseller
     *
     * @return Note
     */
    public function setReseller($reseller)
    {
        $this->reseller = $reseller;

        return $this;
    }

    public function getResellerData()
    {
        return $this->getReseller() ? $this->getReseller()->toArray(Reseller::DEFAULT_DISPLAY_VALUES) : null;
    }
}

