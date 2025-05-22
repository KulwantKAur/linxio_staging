<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InspectionFormTemplate
 */
#[ORM\Table(name: 'inspection_form_template')]
#[ORM\Entity(repositoryClass: 'App\Repository\InspectionFormTemplateRepository')]
class InspectionFormTemplate extends BaseEntity
{
    public const CHECKBOX = 'checkbox';
    public const DEFAULT_DISPLAY_VALUES = [
        'type',
        'title',
        'description',
        'sort',
        'version'
    ];

    /**
     * InspectionFormTemplate constructor.
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->type = $fields['type'] ?? null;
        $this->title = $fields['title'] ?? null;
        $this->description = $fields['description'] ?? null;
        $this->sort = $fields['sort'] ?? null;
        $this->version = $fields['version'] ?? null;
    }

    /**
     * @param array $include
     * @return array
     */
    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('type', $include, true)) {
            $data['type'] = $this->getType();
        }
        if (in_array('title', $include, true)) {
            $data['title'] = $this->getTitle();
        }
        if (in_array('description', $include, true)) {
            $data['description'] = $this->getDescription();
        }
        if (in_array('sort', $include, true)) {
            $data['sort'] = $this->getSort();
        }
        if (in_array('version', $include, true)) {
            $data['version'] = $this->getVersion()->toArray();
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
    #[ORM\Column(name: 'type', type: 'string', length: 255)]
    private $type;

    /**
     * @var string
     */
    #[ORM\Column(name: 'title', type: 'string', length: 255)]
    private $title;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private $description;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'sort', type: 'integer', nullable: true)]
    private $sort;

    /**
     * @var InspectionFormVersion
     */
    #[ORM\ManyToOne(targetEntity: 'InspectionFormVersion', inversedBy: 'templates')]
    #[ORM\JoinColumn(name: 'version_id', referencedColumnName: 'id', onDelete: 'cascade', nullable: false)]
    private $version;


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
     * Set type.
     *
     * @param string $type
     *
     * @return InspectionFormTemplate
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return InspectionFormTemplate
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
     * Set description.
     *
     * @param string|null $description
     *
     * @return InspectionFormTemplate
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set sort.
     *
     * @param int|null $sort
     *
     * @return InspectionFormTemplate
     */
    public function setSort($sort = null)
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * Get sort.
     *
     * @return int|null
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * Set version.
     *
     * @param InspectionFormTemplate $version
     *
     * @return InspectionFormTemplate
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version.
     *
     * @return InspectionFormVersion
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return bool
     */
    public function isCheckbox()
    {
        return $this->type === self::CHECKBOX;
    }
}
