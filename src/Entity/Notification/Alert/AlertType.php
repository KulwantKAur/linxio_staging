<?php

namespace App\Entity\Notification\Alert;

use App\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * AlertType
 *
 *
 * @UniqueEntity(
 *     fields={"name"},
 *     errorPath="name",
 *     message="Alert type with this name already exists."
 * )
 */
#[ORM\Table(name: 'notification_alert_type')]
#[ORM\Entity(repositoryClass: 'App\Repository\Notification\Alert\AlertTypeRepository')]
class AlertType extends BaseEntity
{
    public const VEHICLE_ALERTS = 'vehicle alerts';
    public const ASSET_ALERTS = 'asset alerts';
    public const DEVICE_ALERTS = 'device alerts';
    public const DRIVER_ALERTS = 'driver alerts';
    public const TECHNICAL_ALERTS = 'technical alerts';
    public const TERRITORY_ALERTS = 'territory alerts';
    public const SYSTEM_ALERTS = 'system alerts';
    public const OTHER_ALERTS = 'other alerts';
    public const STRIPE_ALERTS = 'stripe alerts';
    public const XERO_ALERTS = 'xero alerts';
    public const BILLING_ALERTS = 'billing alerts';
    public const BILLING_ALERTS_ADMIN = 'billing alerts admin';

    public const ALERTS_TYPE = [
        self::VEHICLE_ALERTS,
        self::DEVICE_ALERTS,
        self::DRIVER_ALERTS,
        self::TECHNICAL_ALERTS,
        self::TERRITORY_ALERTS,
        self::SYSTEM_ALERTS,
        self::OTHER_ALERTS,
        self::STRIPE_ALERTS,
        self::XERO_ALERTS,
        self::BILLING_ALERTS,
        self::BILLING_ALERTS_ADMIN,
    ];

    public const DISPLAYED_VALUES = [
        'id',
        'name',
        'sort',
    ];

    /**
     * @param array $include
     * @return array
     */
    public function toArray(array $include = []): array
    {
        $data = [];

        if (empty($include)) {
            $include = self::DISPLAYED_VALUES;
        }

        if (in_array('id', $include, true)) {
            $data['id'] = $this->getId();
        }

        if (in_array('name', $include, true)) {
            $data['name'] = $this->getName();
        }

        if (in_array('sort', $include, true)) {
            $data['sort'] = $this->getSort();
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
     *
     *
     * @Assert\Length(
     *      min = 1,
     *      max = 255
     * )
     * @Assert\NotBlank
     */
    #[ORM\Column(name: 'name', type: 'string', length: 255, unique: true)]
    private $name;

    /**
     * @var int
     */
    #[ORM\Column(name: 'sort', type: 'integer', options: ['default' => 10])]
    private $sort;


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
     * @return AlertType
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
     * Set sort.
     *
     * @param int $sort
     *
     * @return AlertType
     */
    public function setSort($sort)
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * Get sort.
     *
     * @return int
     */
    public function getSort()
    {
        return $this->sort;
    }
}
