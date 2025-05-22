<?php

namespace App\Entity\Notification;

use App\Entity\BaseEntity;
use App\Entity\Team;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * TemplateSet
 */
#[ORM\Table(name: 'notification_template_set')]
#[ORM\Entity(repositoryClass: 'App\Repository\Notification\TemplateSetRepository')]
class TemplateSet extends BaseEntity
{
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName()
        ];
    }

    public const DEFAULT_TEMPLATE_SET_NAME = 'default';

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
     * @var Team|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Team')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id', nullable: true)]
    private $team;

    /**
     * @var EventTemplate[]|ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: 'EventTemplate', mappedBy: 'set')]
    private $eventTemplates;

    /**
     * @var string
     */
    #[ORM\Column(name: 'path', type: 'string', length: 255, nullable: true)]
    private $path;

    public function __construct()
    {
        $this->eventTemplates = new ArrayCollection();
    }

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
     * @return TemplateSet
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
     * @return Team|null
     */
    public function getTeam(): ?Team
    {
        return $this->team;
    }

    /**
     * @param Team|null $team
     * @return $this
     */
    public function setTeam(?Team $team)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * @return EventTemplate[]|ArrayCollection
     */
    public function getEventTemplates()
    {
        return $this->eventTemplates;
    }

    /**
     * @param EventTemplate[]|ArrayCollection $eventTemplates
     */
    public function setEventTemplates($eventTemplates): void
    {
        $this->eventTemplates = $eventTemplates;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }
}
