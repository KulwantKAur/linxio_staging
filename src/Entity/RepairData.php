<?php

namespace App\Entity;

use App\Util\AttributesTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * RepairData
 */
#[ORM\Table(name: 'repair_data')]
#[ORM\Entity(repositoryClass: 'App\Repository\RepairDataRepository')]
class RepairData extends BaseEntity
{
    use AttributesTrait;

    public function __construct(array $fields = [])
    {
        $this->title = $fields['title'] ?? null;
        $this->category = $fields['category'] ?? null;
        $this->serviceRecord = $fields['serviceRecord'] ?? null;
        $this->asset = $fields['assetId'] ?? null;
    }

    public function toArray(): array
    {
        // TODO: Implement toArray() method.
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
    #[ORM\Column(name: 'title', type: 'string', length: 255)]
    private $title;

    /**
     * @var ReminderCategory
     */
    #[ORM\ManyToOne(targetEntity: 'ReminderCategory')]
    #[ORM\JoinColumn(name: 'reminder_category', referencedColumnName: 'id', nullable: true)]
    private $category;

    /**
     * @var Vehicle
     */
    #[ORM\ManyToOne(targetEntity: 'Vehicle')]
    #[ORM\JoinColumn(name: 'vehicle_id', referencedColumnName: 'id', nullable: true)]
    private $vehicle;

    /**
     * @var ServiceRecord
     */
    #[ORM\ManyToOne(targetEntity: 'ServiceRecord')]
    #[ORM\JoinColumn(name: 'service_record_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
    private $serviceRecord;

    /**
     * @var Asset
     */
    #[ORM\ManyToOne(targetEntity: 'Asset', inversedBy: 'reminders')]
    #[ORM\JoinColumn(name: 'asset_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private $asset;


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
     * Set title.
     *
     * @param string $title
     *
     * @return RepairData
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param null $category
     * @return $this
     */
    public function setCategory($category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category.
     *
     * @return ReminderCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param $serviceRecord
     * @return $this
     */
    public function setServiceRecord($serviceRecord)
    {
        $this->serviceRecord = $serviceRecord;

        return $this;
    }

    /**
     * @return ServiceRecord
     */
    public function getServiceRecord()
    {
        return $this->serviceRecord;
    }

    /**
     * @param Vehicle $vehicle
     * @return $this
     */
    public function setVehicle(Vehicle $vehicle)
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    /**
     * Get vehicle.
     *
     * @return Vehicle
     */
    public function getVehicle()
    {
        return $this->vehicle;
    }

    public function getAsset(): ?Asset
    {
        return $this->asset;
    }

    public function setAsset(?Asset $asset): self
    {
        $this->asset = $asset;

        return $this;
    }

    public function isVehicleRepair()
    {
        return (bool)$this->getVehicle();
    }

    public function isAssetRepair()
    {
        return (bool)$this->getAsset();
    }

    public function getEntity(): ?BaseEntity
    {
        if ($this->getVehicle()) {
            return $this->getVehicle();
        } elseif ($this->getAsset()) {
            return $this->getAsset();
        } else {
            return null;
        }
    }
}
