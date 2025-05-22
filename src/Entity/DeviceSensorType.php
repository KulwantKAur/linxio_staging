<?php

namespace App\Entity;

use App\Util\AttributesTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueEntity(
 *     fields={"name"},
 *     message="Name is already exists."
 * )
 */
#[ORM\Table(name: 'device_sensor_type')]
#[ORM\Entity(repositoryClass: 'App\Repository\DeviceSensorTypeRepository')]
#[ORM\HasLifecycleCallbacks]
class DeviceSensorType extends BaseEntity
{
    use AttributesTrait;

    public const TOPFLYTECH_IBUTTON_TYPE = 'TOPFLYTECH_IBUTTON_TYPE';
    public const TOPFLYTECH_TEMP_AND_HUMIDITY_TYPE = 'TOPFLYTECH_TEMP_AND_HUMIDITY_TYPE';
    public const TOPFLYTECH_TRACKING_BEACON_TYPE = 'TOPFLYTECH_TRACKING_BEACON_TYPE';

    public const DEFAULT_DISPLAY_VALUES = [
        'id',
        'vendor',
        'name',
        'label',
    ];

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var DeviceVendor|null
     */
    #[ORM\ManyToOne(targetEntity: 'DeviceVendor')]
    #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'id', nullable: true)]
    private $vendor;

    /**
     * @var string
     *
     * @Assert\NotBlank
     */
    #[ORM\Column(name: 'name', type: 'string', nullable: false, unique: true)]
    private $name;

    /**
     * @var string
     *
     * @Assert\NotBlank
     */
    #[ORM\Column(name: 'label', type: 'string', nullable: false)]
    private $label;

    /**
     * @var boolean
     */
    #[ORM\Column(name: 'is_available', type: 'boolean', options: ['default' => '1'])]
    private $isAvailable = true;

    /**
     * @var ArrayCollection|DeviceSensor[]|null
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Sensor', mappedBy: 'type', fetch: 'EXTRA_LAZY')]
    private $sensors;

    /**
     * @param array $fields
     */
    public function __construct(array $fields = [])
    {
        $this->vendor = $fields['vendor'] ?? null;
        $this->name = $fields['name'] ?? null;
        $this->label = $fields['label'] ?? null;
        $this->isAvailable = $fields['isAvailable'] ?? true;
        $this->sensors = new ArrayCollection();
    }

    /**
     * @param array $include
     * @return array
     * @throws \Exception
     */
    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }
        if (in_array('vendorId', $include, true)) {
            $data['vendorId'] = $this->getVendorId();
        }
        if (in_array('vendor', $include, true)) {
            $data['vendor'] = $this->getVendor() ? $this->getVendor()->toArray() : null;
        }
        if (in_array('name', $include, true)) {
            $data['name'] = $this->getName();
        }
        if (in_array('label', $include, true)) {
            $data['label'] = $this->getLabel();
        }
        if (in_array('isAvailable', $include, true)) {
            $data['isAvailable'] = $this->isAvailable();
        }
        if (in_array('sensors', $include, true)) {
            $data['sensors'] = $this->getSensors();
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
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    /**
     * @param bool $isAvailable
     */
    public function setIsAvailable(bool $isAvailable): void
    {
        $this->isAvailable = $isAvailable;
    }

    /**
     * @return DeviceVendor|null
     */
    public function getVendor(): ?DeviceVendor
    {
        return $this->vendor;
    }

    /**
     * @return int|null
     */
    public function getVendorId(): ?int
    {
        return $this->getVendor() ? $this->getVendor()->getId() : null;
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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * @return Sensor[]|ArrayCollection|null
     */
    public function getSensors()
    {
        return $this->sensors;
    }

    /**
     * @param string $typeName
     * @return bool
     */
    public static function isTypeHasTemperature(string $typeName): bool
    {
        return in_array($typeName, [
            self::TOPFLYTECH_TEMP_AND_HUMIDITY_TYPE,
            self::TOPFLYTECH_TRACKING_BEACON_TYPE,
        ]);
    }
}

