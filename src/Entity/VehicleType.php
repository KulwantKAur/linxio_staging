<?php

namespace App\Entity;

use App\Service\File\LocalFileService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * VehicleType
 */
#[ORM\Table(name: 'vehicle_type')]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'my_entity_region')]
#[ORM\Entity(repositoryClass: 'App\Repository\VehicleTypeRepository')]
class VehicleType extends BaseEntity
{
    public const CAR = 'Car';
    public const BUS = 'Bus';
    public const TRUCK = 'Truck';
    public const VAN = 'Van';
    public const UTILITY_VEHICLE = 'Utility Vehicle';
    public const SMALL_TRUCK = 'Small Truck';
    public const LARGE_TRUCK = 'Large Truck';
    public const REF_TRUCK = 'Refrigerated Truck';
    public const TRAILER = 'Trailer';
    public const EXCAVATOR = 'Excavator';
    public const FORKLIFT = 'Forklift';
    public const BULLDOZER = 'Bulldozer';
    public const CONTAINER = 'Container';
    public const PERSON = 'Person';

    public const CAR_ARRAY = [
        self::CAR,
        self::PERSON
    ];
    public const BUS_ARRAY = [
        self::BUS,
        self::VAN
    ];
    public const TRUCK_ARRAY = [
        self::TRUCK,
        self::UTILITY_VEHICLE,
        self::SMALL_TRUCK,
        self::LARGE_TRUCK,
        self::REF_TRUCK,
        self::TRAILER,
        self::EXCAVATOR,
        self::FORKLIFT,
        self::BULLDOZER,
        self::CONTAINER
    ];

    public const ALLOWED_STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_DELETED
    ];

    public const STATUS_ACTIVE = BaseEntity::STATUS_ACTIVE;
    public const STATUS_DELETED = BaseEntity::STATUS_DELETED;

    public const DEFAULT_PICTURE = 'default';
    public const DRIVING_PICTURE = 'driving';
    public const IDLING_PICTURE = 'idling';
    public const STOPPED_PICTURE = 'stopped';

    public const DEFAULT_DISPLAY_VALUES = [
        'name',
        'default',
        'driving',
        'idling',
        'stopped',
        'status',
        'order'
    ];

    public const DEFAULT_PATH = 'uploads/default/';

    public function __construct(array $fields)
    {
        $this->name = $fields['name'] ?? null;
        $this->team = $fields['team'] ?? null;
        $this->defaultPicture = $fields['default'] ?? null;
        $this->drivingPicture = $fields['driving'] ?? null;
        $this->idlingPicture = $fields['idling'] ?? null;
        $this->stoppedPicture = $fields['stopped'] ?? null;
        $this->vehicles = new ArrayCollection();
        $this->status = $fields['status'] ?? self::STATUS_ACTIVE;
        $this->order = $fields['order'] ?? 0;
    }

    public function toArray(array $include = []): array
    {
        $data = [
            'id' => $this->id
        ];

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('name', $include, true)) {
            $data['name'] = $this->getName();
        }
        if (in_array('vehicleCount', $include, true)) {
            $data['vehicleCount'] = $this->getVehiclesCount();
        }
        if (in_array('default', $include, true)) {
            $data['default'] = $this->getDefaultPicturePath();
        }
        if (in_array('driving', $include, true)) {
            $data['driving'] = $this->getDrivingPicturePath();
        }
        if (in_array('idling', $include, true)) {
            $data['idling'] = $this->getIdlingPicturePath();
        }
        if (in_array('stopped', $include, true)) {
            $data['stopped'] = $this->getStoppedPicturePath();
        }
        if (in_array('status', $include, true)) {
            $data['status'] = $this->getStatus();
        }
        if (in_array('order', $include, true)) {
            $data['order'] = $this->getOrder();
        }

        return $data;
    }

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var string
     * @Assert\Length(
     *      min = 1,
     *      max = 250
     * )
     * @Assert\NotBlank
     */
    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    private $name;

    /**
     * @var Team
     */
    #[ORM\ManyToOne(targetEntity: 'Team', inversedBy: 'vehicleTypes')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id', nullable: true)]
    private $team;

    /**
     * @var File
     */
    #[ORM\OneToOne(targetEntity: 'File')]
    #[ORM\JoinColumn(name: 'default_picture_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $defaultPicture;

    /**
     * @var File
     */
    #[ORM\OneToOne(targetEntity: 'File')]
    #[ORM\JoinColumn(name: 'driving_picture_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $drivingPicture;

    /**
     * @var File
     */
    #[ORM\OneToOne(targetEntity: 'File')]
    #[ORM\JoinColumn(name: 'idling_picture_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $idlingPicture;

    /**
     * @var File
     */
    #[ORM\OneToOne(targetEntity: 'File')]
    #[ORM\JoinColumn(name: 'stopped_picture_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $stoppedPicture;

    /**
     * @var ArrayCollection|Vehicle[]
     */
    #[ORM\OneToMany(targetEntity: 'Vehicle', mappedBy: 'type', fetch: 'EXTRA_LAZY')]
    private $vehicles;

    /**
     * @var string
     */
    #[ORM\Column(name: 'status', type: 'string', length: 100, nullable: true)]
    private $status;

    /**
     * @var int
     */
    #[ORM\Column(name: 'sort', type: 'integer', nullable: false, options: ['default' => 0])]
    private $order = 0;

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
     * Set name.
     *
     * @param string $name
     *
     * @return VehicleType
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
     * Set team
     *
     * @param Team $team
     *
     * @return VehicleType
     */
    public function setTeam(?Team $team)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * Get team
     *
     * @return Team
     */
    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setDefaultPicture(File $picture)
    {
        $this->defaultPicture = $picture;

        return $this;
    }

    public function getDefaultPicture(): ?File
    {
        return $this->defaultPicture;
    }

    public function getDefaultPicturePath()
    {
        return $this->defaultPicture ?
            LocalFileService::VEHICLE_TYPE_PUBLIC_PATH . $this->defaultPicture->getName()
            : self::DEFAULT_PATH . 'default.png';
    }

    public function setDrivingPicture(File $picture)
    {
        $this->drivingPicture = $picture;

        return $this;
    }

    public function getDrivingPicture(): ?File
    {
        return $this->drivingPicture;
    }

    public function getDrivingPicturePath()
    {
        return $this->drivingPicture ?
            LocalFileService::VEHICLE_TYPE_PUBLIC_PATH . $this->drivingPicture->getName()
            : self::DEFAULT_PATH . 'driving.png';
    }

    public function setIdlingPicture(File $picture)
    {
        $this->idlingPicture = $picture;

        return $this;
    }

    public function getIdlingPicture(): ?File
    {
        return $this->idlingPicture;
    }

    public function getIdlingPicturePath()
    {
        return $this->idlingPicture ?
            LocalFileService::VEHICLE_TYPE_PUBLIC_PATH . $this->idlingPicture->getName()
            : self::DEFAULT_PATH . 'idling.png';
    }

    public function setStoppedPicture(File $picture)
    {
        $this->stoppedPicture = $picture;

        return $this;
    }

    public function getStoppedPicture(): ?File
    {
        return $this->stoppedPicture;
    }

    public function getStoppedPicturePath()
    {
        return $this->stoppedPicture ?
            LocalFileService::VEHICLE_TYPE_PUBLIC_PATH . $this->stoppedPicture->getName()
            : self::DEFAULT_PATH . 'stopped.png';
    }

    public function convertToOldType(): string
    {
        if (in_array($this->name, self::CAR_ARRAY)) {
            return self::CAR;
        } elseif (in_array($this->name, self::BUS_ARRAY)) {
            return self::BUS;
        } elseif (in_array($this->name, self::TRUCK_ARRAY)) {
            return self::TRUCK;
        } else {
            return self::CAR;
        }
    }

    public function getVehiclesCount()
    {
        return $this->vehicles->count();
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return VehicleType
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function setOrder($order): self
    {
        $this->order = $order;

        return $this;
    }

    public function getOrder(): int
    {
        return $this->order;
    }
}
