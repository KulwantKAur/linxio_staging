<?php

namespace App\Entity;

use App\Entity\Tracker\TrackerHistory;
use App\Util\AttributesTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: 'App\Repository\FuelStationRepository')]
class FuelStation extends BaseEntity
{
    use AttributesTrait;

    public function __construct(array $fields)
    {
        $this->siteId = $fields['siteId'] ?? null;
        $this->stationId = $fields['stationId'] ?? null;
        $this->stationName = $fields['stationName'] ?? null;
        $this->lng = $fields['lng'] ?? null;
        $this->lat = $fields['lat'] ?? null;
        $this->address = $fields['address'] ?? null;
        $this->team = $fields['team'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'siteId' => $this->getSiteId(),
            'stationId' => $this->getStationId(),
            'stationName' => $this->getStationName(),
            'lng' => $this->getLng(),
            'lat' => $this->getLat(),
            'address' => $this->getAddress(),
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
     * @var string
     */
    #[ORM\Column(name: 'site_id', type: 'string', length: 255, nullable: true)]
    private $siteId;

    /**
     * @var string
     */
    #[ORM\Column(name: 'station_id', type: 'string', length: 255, nullable: true)]
    private $stationId;

    /**
     * @var string
     * @Assert\NotNull()
     * @Assert\Length(max=255)
     */
    #[ORM\Column(name: 'station_name', type: 'string', length: 255, nullable: false)]
    private $stationName;

    /**
     * @var string
     * @Assert\NotNull()
     */
    #[ORM\Column(name: 'lng', type: 'decimal', precision: 11, scale: 8, nullable: false)]
    private $lng;

    /**
     * @var string
     * @Assert\NotNull()
     */
    #[ORM\Column(name: 'lat', type: 'decimal', precision: 11, scale: 8, nullable: false)]
    private $lat;

    /**
     * @var string
     */
    #[ORM\Column(name: 'address', type: 'text', nullable: true)]
    private $address;

    /**
     * @var Team
     *
     * @Assert\NotNull()
     */
    #[ORM\ManyToOne(targetEntity: 'Team', inversedBy: 'fuelStation', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $team;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setTeam(Team $team)
    {
        $this->team = $team;

        return $this;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }

    public function setSiteId(?string $siteId): self
    {
        $this->siteId = $siteId;

        return $this;
    }

    public function getSiteId(): ?string
    {
        return $this->siteId;
    }

    public function getStationName(): ?string
    {
        return $this->stationName;
    }

    public function setStationName(string $location): self
    {
        $this->stationName = $location;

        return $this;
    }

    public function setLng($lng)
    {
        $this->lng = isset($lng) && TrackerHistory::isCoordinateValid('longitude', $lng) ? $lng : null;

        return $this;
    }

    public function getLng()
    {
        return $this->lng;
    }

    public function setLat($lat)
    {
        $this->lat = isset($lat) && TrackerHistory::isCoordinateValid('latitude', $lat) ? $lat : null;

        return $this;
    }

    public function getLat()
    {
        return $this->lat;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function setStationId(string $stationId): self
    {
        $this->stationId = $stationId;

        return $this;
    }

    public function getStationId(): ?string
    {
        return $this->stationId;
    }
}
