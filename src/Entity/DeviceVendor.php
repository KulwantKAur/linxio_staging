<?php

namespace App\Entity;

use App\Entity\Tracker\TrackerAuthUnknown;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * DeviceVendor
 */
#[ORM\Table(name: 'device_vendor')]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'my_entity_region')]
#[ORM\Entity(repositoryClass: 'App\Repository\DeviceVendorRepository')]
class DeviceVendor extends BaseEntity
{
    public const VENDOR_TELTONIKA = 'Teltonika';
    public const VENDOR_TOPFLYTECH = 'Topflytech';
    public const VENDOR_ULBOTECH = 'Ulbotech';
    public const VENDOR_PIVOTEL = 'Pivotel';
    public const DIGITAL_MATTER = 'Digital Matter';
    public const VENDOR_TRACCAR = 'Other';
    public const VENDOR_STREAMAX = 'Streamax';

    public const TOPFLYTECH_ALIAS = 'Linxio';

    public const VENDOR_ALIAS = [
        self::VENDOR_TELTONIKA => self::VENDOR_TELTONIKA,
        self::VENDOR_TOPFLYTECH => self::TOPFLYTECH_ALIAS,
        self::VENDOR_ULBOTECH => self::VENDOR_ULBOTECH,
        self::VENDOR_PIVOTEL => self::VENDOR_PIVOTEL,
        self::DIGITAL_MATTER => self::DIGITAL_MATTER,
        self::VENDOR_TRACCAR => self::VENDOR_TRACCAR,
        self::VENDOR_STREAMAX => self::VENDOR_STREAMAX,
    ];

    public const DEFAULT_DISPLAY_VALUES = [];

    /**
     * DeviceVendor constructor.
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->name = $fields['name'];
        $this->alias = $fields['alias'];
        $this->models = new ArrayCollection();
    }

    public function toArray(array $include = [], ?User $user = null): array
    {
        $data = [];
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }
        if (in_array('name', $include, true)) {
            $data['name'] = ($user && !$user->isInAdminTeam()) ? $this->getAlias() : $this->getName();
        }
        if (in_array('models', $include, true)) {
            $data['models'] = $this->getModelsArray($user);
        }
        if (in_array('alias', $include, true)) {
            $data['alias'] = $this->getAlias();
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
     */
    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    private $name;

    #[ORM\OneToMany(targetEntity: 'DeviceModel', mappedBy: 'vendor')]
    private $models;

    /**
     * @var ArrayCollection|TrackerAuthUnknown[]
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Tracker\TrackerAuthUnknown', mappedBy: 'vendor')]
    private $trackerAuthUnknown;

    /**
     * @var string
     */
    #[ORM\Column(name: 'alias', type: 'string', length: 255, nullable: true)]
    private $alias;

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
     * @return DeviceVendor
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

    public function getModels(): Collection
    {
        return $this->models;
    }

    public function getModelsSorted(?User $user = null): Collection
    {
        $orderField = ($user && !$user->isInAdminTeam()) ? 'alias' : 'name';

        return $this->getModels()->matching(
            Criteria::create()->orderBy([$orderField => Criteria::ASC])
        );
    }

    public function getModelsArray(?User $user = null): array
    {
        return array_map(function ($model) use ($user) {
            return $model->toArray(['name', 'alias', 'usage'], $user);
        }, $this->getModelsSorted($user)->toArray());
    }

    /**
     * @return TrackerAuthUnknown[]|ArrayCollection
     */
    public function getTrackerAuthUnknowns()
    {
        return $this->trackerAuthUnknown;
    }

    /**
     * @param TrackerAuthUnknown[]|ArrayCollection $trackerAuthUnknown
     */
    public function setTrackerAuthUnknowns($trackerAuthUnknown): void
    {
        $this->trackerAuthUnknown = $trackerAuthUnknown;
    }

    public function setAlias($alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    /**
     * @return bool
     */
    public function hasExternalVoltage(): bool
    {
        switch ($this->getName()) {
            case self::VENDOR_TOPFLYTECH:
            case self::VENDOR_TELTONIKA:
            case self::VENDOR_ULBOTECH:
                return true;
            default:
                return false;
        }
    }

    /**
     * @return bool
     */
    public function hasSatellites(): bool
    {
        switch ($this->getName()) {
            case self::VENDOR_TOPFLYTECH:
            case self::VENDOR_TELTONIKA:
                return true;
            default:
                return false;
        }
    }
}
