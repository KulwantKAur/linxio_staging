<?php

namespace App\Entity;

use App\Util\AttributesTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'route_start_area')]
#[ORM\UniqueConstraint(columns: ['route_id', 'area_id'])]
#[ORM\Index(columns: ['route_id', 'area_id'], name: 'route_start_area_route_id_area_id_idx')]
#[ORM\Entity(repositoryClass: 'App\Repository\RouteStartAreaRepository')]
class RouteStartArea extends BaseEntity
{
    use AttributesTrait;

    public const DEFAULT_DISPLAY_VALUES = [
        'route',
        'area'
    ];

    public function __construct(array $fields = [])
    {
        $this->route = $fields['route'] ?? null;
        $this->area = $fields['area'] ?? null;
    }

    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('route', $include, true)) {
            $data['route'] = $this->getRoute();
        }
        if (in_array('area', $include, true)) {
            $data['area'] = $this->getArea();
        }

        return $data;
    }

    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\ManyToOne(targetEntity: 'Area', inversedBy: 'routeStartArea')]
    #[ORM\JoinColumn(name: 'area_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $area;

    #[ORM\ManyToOne(targetEntity: 'Route', inversedBy: 'routeStartArea')]
    #[ORM\JoinColumn(name: 'route_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $route;

    public function getId()
    {
        return $this->id;
    }

    public function setArea(Area $area)
    {
        $this->area = $area;

        return $this;
    }

    public function getArea()
    {
        return $this->area;
    }

    public function setRoute(Route $route)
    {
        $this->route = $route;

        return $this;
    }

    public function getRoute()
    {
        return $this->route;
    }
}
