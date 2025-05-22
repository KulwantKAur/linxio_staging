<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Integration
 */
#[ORM\Table(name: 'integration')]
#[ORM\Entity(repositoryClass: 'App\Repository\IntegrationRepository')]
class Integration extends BaseEntity
{
    public const SOLBOX = 'SolBox';
    public const PRISM = 'Prism';
    public const FLEETIO = 'Fleetio';
    public const VWORK = 'vWork';
    public const FUSE = 'Fuse';
    public const STREAMAX = 'Linxio Vision 3.0';
    public const LOGMASTER = 'Logmaster';
    public const GEARBOX = 'Gearbox';

    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->getId();
        $data['name'] = $this->getName();

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
     * @return Integration
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
}
