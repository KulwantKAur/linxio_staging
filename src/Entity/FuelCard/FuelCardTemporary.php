<?php

namespace App\Entity\FuelCard;

use Doctrine\ORM\Mapping as ORM;

/**
 * FuelCardTemporary
 */
#[ORM\Table(name: 'fuel_card_temporary')]
#[ORM\Entity(repositoryClass: 'App\Repository\FuelCard\FuelCardTemporaryRepository')]
class FuelCardTemporary
{

    public const DISPLAYED_VALUES = [
        'id',
        'vehicleOriginal',
        'refueledFuelTypeOriginal',
        'comments',
    ];

    public function __construct(array $fields)
    {
        $this->setRefueledFuelTypeOriginal($fields['refueledFuelTypeOriginal'] ?? null);
        $this->setVehicleOriginal($fields['vehicleOriginal'] ?? null);
        $this->setComments($fields['comments'] ?? null);
    }

    /**
     * @param array $include
     * @return array
     * @throws \Exception
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

        if (in_array('vehicleOriginal', $include, true)) {
            $data['vehicleOriginal'] = $this->getVehicleOriginal();
        }

        if (in_array('refueledFuelTypeOriginal', $include, true)) {
            $data['refueledFuelTypeOriginal'] = $this->getRefueledFuelTypeOriginal();
        }

        if (in_array('comments', $include, true)) {
            $data['comments'] = $this->getComments();
        }

        return $data;
    }

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var array|null
     */
    #[ORM\Column(name: 'comments', type: 'json', length: 255, nullable: true)]
    private $comments;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'vehicle_original', type: 'string', length: 255, nullable: true)]
    private $vehicleOriginal;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'refueled_fuel_type_original', type: 'string', length: 255, nullable: true)]
    private $refueledFuelTypeOriginal;

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
     * Set comments.
     *
     * @param array $comments
     *
     * @return FuelCardTemporary
     */
    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Get comments.
     *
     * @return array
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set vehicleOriginal.
     *
     * @param string|null $vehicleOriginal
     *
     * @return FuelCardTemporary
     */
    public function setVehicleOriginal($vehicleOriginal = null)
    {
        $this->vehicleOriginal = $vehicleOriginal;

        return $this;
    }

    /**
     * Get vehicleOriginal.
     *
     * @return string|null
     */
    public function getVehicleOriginal()
    {
        return $this->vehicleOriginal;
    }

    /**
     * Set refueledFuelTypeOriginal.
     *
     * @param string|null $refueledFuelTypeOriginal
     *
     * @return FuelCardTemporary
     */
    public function setRefueledFuelTypeOriginal($refueledFuelTypeOriginal = null)
    {
        $this->refueledFuelTypeOriginal = $refueledFuelTypeOriginal;

        return $this;
    }

    /**
     * Get refueledFuelTypeOriginal.
     *
     * @return string|null
     */
    public function getRefueledFuelTypeOriginal()
    {
        return $this->refueledFuelTypeOriginal;
    }
}
