<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * InspectionFormData
 */
#[ORM\Table(name: 'inspection_form_data')]
#[ORM\Entity(repositoryClass: 'App\Repository\InspectionFormDataRepository')]
class InspectionFormData extends BaseEntity
{
    public const STATUS_PASS = 'pass';
    public const STATUS_FAIL = 'fail';

    public const DEFAULT_DISPLAY_VALUES = [
        'form',
        'values',
        'status',
        'statusRatio',
        'duration',
        'user',
        'vehicle',
        'date',
        'files'
    ];

    public const LIST_DISPLAY_VALUES = [
        'form',
        'status',
        'statusRatio',
        'duration',
        'user',
        'vehicle',
        'date'
    ];

    /**
     * InspectionFormData constructor.
     * @param array $fields
     * @throws \Exception
     */
    public function __construct(array $fields)
    {
        $this->form = $fields['form'] ?? null;
        $this->user = $fields['user'] ?? null;
        $this->vehicle = $fields['vehicle'] ?? null;
        $this->version = $fields['version'] ?? null;
        $this->createdAt = new \DateTime();
        $this->values = new ArrayCollection();
        $this->files = new ArrayCollection();
    }

    /**
     * @param array $include
     * @return array
     * @throws \Exception
     */
    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('form', $include, true)) {
            $data['form'] = $this->getForm()->toArray();
        }
        if (in_array('values', $include, true)) {
            $data['values'] = $this->getValuesData();
        }
        if (in_array('status', $include, true)) {
            $data['status'] = $this->getStatus();
        }
        if (in_array('statusRatio', $include, true)) {
            $data['statusRatio'] = $this->getStatusRatio();
        }
        if (in_array('duration', $include, true)) {
            $data['duration'] = $this->getDuration();
        }
        if (in_array('user', $include, true)) {
            $data['user'] = $this->getUser()->toArray(User::SIMPLE_VALUES);
        }
        if (in_array('vehicle', $include, true)) {
            $data['vehicle'] = $this->getVehicle()->toArray(['regNo', 'model']);
        }
        if (in_array('date', $include, true)) {
            $data['date'] = $this->formatDate($this->getCreatedAt());
        }
        if (in_array('files', $include, true)) {
            $data['files'] = $this->getFilesData();
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
     * @var InspectionForm
     */
    #[ORM\ManyToOne(targetEntity: 'InspectionForm')]
    #[ORM\JoinColumn(name: 'form_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: false)]
    private $form;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: false)]
    private $user;

    /**
     * @var Vehicle
     */
    #[ORM\ManyToOne(targetEntity: 'Vehicle')]
    #[ORM\JoinColumn(name: 'vehicle_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: false)]
    private $vehicle;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * @var InspectionFormVersion
     */
    #[ORM\ManyToOne(targetEntity: 'InspectionFormVersion')]
    #[ORM\JoinColumn(name: 'version_id', referencedColumnName: 'id', onDelete: 'cascade', nullable: false)]
    private $version;

    /**
     * @var ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: 'InspectionFormDataValue', mappedBy: 'formData')]
    private $values;

    /**
     * @var ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: 'InspectionFormFile', mappedBy: 'formData', cascade: ['persist'])]
    private $files;


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
     * Set form.
     *
     * @param InspectionForm $form
     *
     * @return InspectionFormData
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
     * Set user.
     *
     * @param User $user
     *
     * @return InspectionFormData
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set vehicle.
     *
     * @param Vehicle $vehicle
     *
     * @return InspectionFormData
     */
    public function setVehicle($vehicle)
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    /**
     * Get vehicle.
     *
     * @return Vehicle
     */
    public function getVehicle()
    {
        return $this->vehicle;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return InspectionFormData
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set version.
     *
     * @param InspectionFormTemplate $version
     *
     * @return InspectionFormData
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version.
     *
     * @return InspectionFormTemplate
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return array
     */
    public function getValuesData()
    {
        return array_map(
            function ($value) {
                return $value->toArray();
            },
            $this->values->toArray()
        );
    }

    /**
     * @return ArrayCollection
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @return Team
     */
    public function getTeam()
    {
        return $this->getVehicle()->getTeam();
    }

    /**
     * @return int
     */
    public function getTeamId()
    {
        return $this->getVehicle()->getTeam()->getId();
    }

    /**
     * @return string
     */
    public function getFormTitle()
    {
        return $this->getForm()->getTitle();
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        $status = self::STATUS_PASS;

        foreach ($this->getValues() as $value) {
            if (!$value->isPass()) {
                $status = self::STATUS_FAIL;
            }
        }

        return $status;
    }

    /**
     * @return string
     */
    public function getStatusRatio()
    {
        $countAll = $this->getValues()->count();
        $passCount = 0;
        foreach ($this->getValues() as $value) {
            if ($value->isPass()) {
                $passCount++;
            }
        }

        return $passCount . '/' . $countAll;
    }

    /**
     * @return int
     */
    public function getDuration()
    {
        $duration = 0;
        foreach ($this->getValues() as $value) {
            $duration += $value->getTime();
        }

        return $duration;
    }

    /**
     * @return array
     */
    public function getFilesData()
    {
        return array_map(
            function ($file) {
                return $file->toArray();
            },
            $this->files->toArray()
        );
    }

    /**
     * @return ArrayCollection
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param InspectionFormFile $file
     */
    public function addFile(InspectionFormFile $file)
    {
        $this->files->add($file);
    }
}
