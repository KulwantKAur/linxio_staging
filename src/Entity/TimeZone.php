<?php

namespace App\Entity;

use App\Util\CarbonTimeZone;
use Doctrine\ORM\Mapping as ORM;

/**
 * TimeZone
 */
#[ORM\Table(name: 'time_zone')]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'my_entity_region')]
#[ORM\Entity(repositoryClass: 'App\Repository\TimeZoneRepository')]
class TimeZone extends BaseEntity
{
    public const DEFAULT_TIMEZONE = [
        'name' => 'Australia/Sydney'
    ];

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

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
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Get displayName
     *
     * @return string
     */
    public function getDisplayName()
    {
        $timezone = new CarbonTimeZone($this->getName());

        return '(UTC' . $timezone->toOffsetName() . ') ' . $timezone->toRegionName();
    }

    /**
     * Get displayName
     *
     * @return string
     */
    public function getName()
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
     * @return int
     * @throws \Exception
     */
    public function getOffset(){
        $timezoneObject = new \DateTimeZone($this->getName());

        return $timezoneObject->getOffset(new \DateTime());
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'displayName' => $this->getDisplayName(),
            'name' => $this->getName()
        ];
    }
}

