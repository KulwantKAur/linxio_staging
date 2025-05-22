<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * InspectionForm
 */
#[ORM\Table(name: 'inspection_form')]
#[ORM\Entity(repositoryClass: 'App\Repository\InspectionFormRepository')]
class InspectionForm extends BaseEntity
{

    public const DEFAULT_DISPLAY_VALUES = [
        'versions',
        'templates'
    ];

    /**
     * InspectionForm constructor.
     * @param array $fields
     * @throws \Exception
     */
    public function __construct(array $fields)
    {
        $this->title = $fields['title'] ?? null;
        $this->createdBy = $fields['createdBy'] ?? null;
        $this->createdAt = new \DateTime();
    }

    /**
     * @param array $include
     * @return array
     */
    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->getId();
        $data['title'] = $this->getTitle();
        $data['isDefault'] = $this->getIsDefault();

        if (in_array('versions', $include, true)) {
            $data['versions'] = $this->getVersionsData();
        }

        if (in_array('templates', $include, true)) {
            $data['templates'] = $this->getLastVersionTemplatesArray();
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
    #[ORM\Column(name: 'title', type: 'string', length: 255)]
    private $title;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $createdBy;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private $updatedAt;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'updated_by', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $updatedBy;

    /**
     * @var ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: 'InspectionFormVersion', mappedBy: 'form', fetch: 'EXTRA_LAZY')]
    private $versions;

    /**
     * @var boolean
     */
    #[ORM\Column(name: 'is_default', type: 'boolean', options: ['default' => '0'])]
    private $isDefault = false;

    /**
     * Many forms have Many teams.
     */
    #[ORM\JoinTable(name: 'inspection_form_teams')]
    #[ORM\ManyToMany(targetEntity: 'Team', inversedBy: 'inspectionForms', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    private $teams;

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
     * Set title.
     *
     * @param string $title
     *
     * @return InspectionForm
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return InspectionForm
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set createdBy
     *
     * @param User $createdBy
     *
     * @return InspectionForm
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return InspectionForm
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set updatedBy
     *
     * @param User $updatedBy
     *
     * @return InspectionForm
     */
    public function setUpdatedBy(?User $updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function getVersions()
    {
        return $this->versions;
    }

    /**
     * @return InspectionFormVersion
     */
    public function getLastVersion(): InspectionFormVersion
    {
        return $this->versions->last();
    }

    /**
     * @return array
     */
    public function getLastVersionTemplatesArray()
    {
        return $this->getLastVersion()->getTemplatesData();
    }

    /**
     * @return ArrayCollection
     */
    public function getLastVersionTemplates()
    {
        return $this->getLastVersion()->getTemplates();
    }

    /**
     * @return array
     */
    public function getVersionsData()
    {
        return array_map(
            function ($version) {
                return $version->toArray();
            },
            $this->versions->toArray()
        );
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setIsDefault(bool $value)
    {
        $this->isDefault = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    public function getTeamEntities()
    {
        return $this->teams;
    }

    /**
     * @param Team $team
     * @return $this
     */
    public function addTeam(Team $team)
    {
        if ($this->isDefault) {
            $team->removeFromAllInspectionForms();
        } elseif (!$this->teams->contains($team)) {
            $team->removeFromAllInspectionForms();
            $this->teams->add($team);
        }

        return $this;
    }

    /**
     * @param Team $team
     * @return $this
     */
    public function removeTeam(Team $team)
    {
        $this->teams->removeElement($team);

        return $this;
    }
}
