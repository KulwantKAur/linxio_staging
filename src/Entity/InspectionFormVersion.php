<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * InspectionFormVersion
 */
#[ORM\Table(name: 'inspection_form_version')]
#[ORM\Entity(repositoryClass: 'App\Repository\InspectionFormVersionRepository')]
class InspectionFormVersion extends BaseEntity
{
    public function __construct(array $fields)
    {
        $this->version = $fields['version'] ?? null;
        $this->form = $fields['form'] ?? null;
        $this->createdAt = new \DateTime();
    }

    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->id;

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
     * @var int
     */
    #[ORM\Column(name: 'version', type: 'integer')]
    private $version;

    /**
     * @var InspectionForm
     */
    #[ORM\ManyToOne(targetEntity: 'InspectionForm', inversedBy: 'versions')]
    #[ORM\JoinColumn(name: 'form_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: false)]
    private $form;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * @var ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: 'InspectionFormTemplate', mappedBy: 'version', fetch: 'EXTRA_LAZY')]
    private $templates;

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
     * Set version.
     *
     * @param int $version
     *
     * @return InspectionFormVersion
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version.
     *
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set form.
     *
     * @param InspectionForm $form
     *
     * @return InspectionFormVersion
     */
    public function setForm($form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * Get form.
     *
     * @return InspectionForm
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return InspectionFormVersion
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
     * @return ArrayCollection
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * @return array
     */
    public function getTemplatesData()
    {
        return array_map(
            function ($template) {
                return $template->toArray(InspectionFormTemplate::DEFAULT_DISPLAY_VALUES);
            },
            $this->templates->matching(
                Criteria::create()
                    ->orderBy(['sort' => Criteria::ASC, 'id' => Criteria::ASC])
            )->toArray()
        );
    }
}
