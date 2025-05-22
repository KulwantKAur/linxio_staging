<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Importance
 */
#[ORM\Table(name: 'importance')]
#[ORM\Entity(repositoryClass: 'App\Repository\ImportanceRepository')]
class Importance
{
    public const TYPE_LOW = 'low';
    public const TYPE_NORMAL = 'normal';
    public const TYPE_AVERAGE = 'average';
    public const TYPE_IMPORTANT = 'important';
    public const TYPE_CRITICAL = 'critical';

    public const DEFAULT_DISPLAY_VALUES = [
        'id',
        'name',
    ];

    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('name', $include, true)) {
            $data['name'] = $this->getName();
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
     * @return Importance
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
