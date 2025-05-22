<?php

namespace App\Entity\FuelType;

use App\Entity\BaseEntity;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * FuelType
 */
#[ORM\Table(name: 'fuel_type')]
#[ORM\Entity(repositoryClass: 'App\Repository\FuelType\FuelTypeRepository')]
class FuelType extends BaseEntity
{
    public const FUEL_TYPE_DIESEL = 'Diesel';
    public const FUEL_TYPE_PETROL = 'Petrol';
    public const FUEL_TYPE_PETROL_GASOLINE = 'Petrol/Gasoline';
    public const FUEL_TYPE_GAS = 'Gas';
    public const FUEL_TYPE_LPG = 'LPG';
    public const FUEL_TYPE_ELECTRIC = 'Electric engine';

    public const DISPLAYED_VALUES = [
        'id',
        'name',
    ];

    /**
     * @param array $include
     * @return array
     */
    public function toArray(array $include = [], ?User $user = null): array
    {
        $data = [];

        if (empty($include)) {
            $include = self::DISPLAYED_VALUES;
        }

        if (in_array('id', $include, true)) {
            $data['id'] = $this->getId();
        }

        if (in_array('name', $include, true)) {
            if ($user && $user->getClient()?->isChevron()) {
                $data['name'] = self::convertFuelTypeForChevron($this->getName());
            } else {
                $data['name'] = $this->getName();
            }
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
    #[ORM\Column(name: 'name', type: 'string', length: 255, unique: true)]
    private $name;

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
     * @return FuelType
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

    public static function convertFuelTypeForChevron(string $fuelType)
    {
        return match ($fuelType) {
            FuelType::FUEL_TYPE_PETROL => FuelType::FUEL_TYPE_PETROL_GASOLINE,
            FuelType::FUEL_TYPE_GAS => FuelType::FUEL_TYPE_LPG,
            default => $fuelType
        };
    }
}
