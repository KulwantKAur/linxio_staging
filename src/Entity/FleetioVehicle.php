<?php

namespace App\Entity;

use App\Repository\FleetioVehicleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FleetioVehicleRepository::class)]
class FleetioVehicle extends BaseEntity
{
    public function __construct(array $fields)
    {
        $this->vehicle = $fields['vehicle'] ?? null;
        $this->fleetioVehicleId = $fields['fleetioVehicleId'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'vehicle' => $this->getVehicle()->toArray(),
            'fleetioVehicleId' => $this->getFleetioVehicleId()
        ];
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    /**
     * @var Vehicle
     */
    #[ORM\OneToOne(targetEntity: 'App\Entity\Vehicle')]
    #[ORM\JoinColumn(name: 'vehicle_id', referencedColumnName: 'id')]
    private $vehicle;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', name: 'fleetio_vehicle_id')]
    private $fleetioVehicleId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVehicle(): Vehicle
    {
        return $this->vehicle;
    }

    public function setVehicle(Vehicle $vehicle): self
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    public function getFleetioVehicleId(): int
    {
        return $this->fleetioVehicleId;
    }

    public function setFleetioVehicleId(int $fleetioVehicleId): self
    {
        $this->fleetioVehicleId = $fleetioVehicleId;

        return $this;
    }
}
