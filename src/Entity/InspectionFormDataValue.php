<?php

namespace App\Entity;

use App\Service\File\LocalFileService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * InspectionFormDataValue
 */
#[ORM\Table(name: 'inspection_form_data_value')]
#[ORM\Entity(repositoryClass: 'App\Repository\InspectionFormDataValueRepository')]
class InspectionFormDataValue extends BaseEntity
{
    public const TRUE_VALUE = 'true';
    public const FALSE_VALUE = 'false';

    public function __construct(array $fields)
    {
        $this->formData = $fields['formData'] ?? null;
        $this->formTemplate = $fields['formTemplate'] ?? null;
        $this->value = $fields['value'] ?? null;
        $this->time = $fields['time'] ?? null;
        $this->note = $fields['note'] ?? null;
        $this->file = $fields['file'] ?? null;
    }

    /**
     * @param array $include
     * @return array
     */
    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->id;
        $data['template'] = $this->getFormTemplate()->toArray();
        $data['value'] = $this->getValueData();
        $data['time'] = $this->getTime();
        $data['note'] = $this->getNote();
        $data['file'] = $this->getFileData();

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
     * @var InspectionFormData
     */
    #[ORM\ManyToOne(targetEntity: 'InspectionFormData', inversedBy: 'values')]
    #[ORM\JoinColumn(name: 'if_data_id', referencedColumnName: 'id', onDelete: 'cascade', nullable: false)]
    private $formData;

    /**
     * @var InspectionFormTemplate
     */
    #[ORM\ManyToOne(targetEntity: 'InspectionFormTemplate')]
    #[ORM\JoinColumn(name: 'if_template_id', referencedColumnName: 'id', onDelete: 'cascade', nullable: false)]
    private $formTemplate;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'value', type: 'string', length: 255, nullable: true)]
    private $value;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'time', type: 'integer', nullable: true)]
    private $time;

    /**
     * @var string
     */
    #[ORM\Column(name: 'note', type: 'text', nullable: true)]
    private $note;

    
    #[ORM\OneToOne(targetEntity: 'File')]
    #[ORM\JoinColumn(name: 'file_id', referencedColumnName: 'id')]
    private $file;


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
     * Set formData.
     *
     * @param int $formData
     *
     * @return InspectionFormDataValue
     */
    public function setFormData($formData)
    {
        $this->formData = $formData;

        return $this;
    }

    /**
     * Get formData.
     *
     * @return InspectionFormData
     */
    public function getFormData()
    {
        return $this->formData;
    }

    /**
     * Set formTemplate.
     *
     * @param InspectionFormTemplate $formTemplate
     *
     * @return InspectionFormDataValue
     */
    public function setFormTemplate($formTemplate)
    {
        $this->formTemplate = $formTemplate;

        return $this;
    }

    /**
     * Get formTemplate.
     *
     * @return InspectionFormTemplate
     */
    public function getFormTemplate()
    {
        return $this->formTemplate;
    }

    /**
     * Set value.
     *
     * @param string|null $value
     *
     * @return InspectionFormDataValue
     */
    public function setValue($value = null)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set time.
     *
     * @param int|null $time
     *
     * @return InspectionFormDataValue
     */
    public function setTime($time = null)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time.
     *
     * @return int|null
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @return bool
     */
    public function getCheckboxStatus()
    {
        return $this->value === self::TRUE_VALUE;
    }

    /**
     * @return bool
     */
    public function isPass()
    {
        $status = false;

        switch ($this->getFormTemplate()->getType()) {
            case InspectionFormTemplate::CHECKBOX:
                $status = $this->getCheckboxStatus();
        }

        return $status;
    }

    /**
     * @return mixed|string|null
     */
    public function getValueData()
    {
        $value = $this->value;

        switch ($this->getFormTemplate()->getType()) {
            case InspectionFormTemplate::CHECKBOX:
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                break;
        }

        return $value;
    }

    /**
     * @param $note
     * @return mixed
     */
    public function setNote(string $note)
    {
        $this->note = $note;

        return $note;
    }

    /**
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param File $file
     * @return $this
     */
    public function setFile(File $file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return array
     */
    public function getFileData()
    {
        if ($this->getFile()) {
            $this->file->setPath(LocalFileService::INSPECTION_FORM_PUBLIC_PATH);

            return $this->getFile()->toArray();
        }

        return null;
    }
}
