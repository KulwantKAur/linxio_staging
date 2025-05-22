<?php

namespace App\Entity;

use App\Service\File\LocalFileService;
use Doctrine\ORM\Mapping as ORM;

/**
 * InspectionFormFile
 */
#[ORM\Table(name: 'inspection_form_file')]
#[ORM\Entity(repositoryClass: 'App\Repository\InspectionFormFileRepository')]
class InspectionFormFile extends BaseEntity
{
    public const TYPE_SIGN = 'sign';

    /**
     * InspectionFormFile constructor.
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->file = $fields['file'];
        $this->formData = $fields['formData'];
        $this->type = $fields['type'];
    }

    /**
     * @param array $include
     * @return array
     * @throws \Exception
     */
    public function toArray(array $include = []): array
    {
        $data['id'] = $this->id;
        $data['file'] = $this->getFileData();
        $data['type'] = $this->getType();

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
     * @var File
     */
    #[ORM\OneToOne(targetEntity: 'File')]
    #[ORM\JoinColumn(name: 'file_id', referencedColumnName: 'id', nullable: true)]
    private $file;

    /**
     * @var InspectionFormData
     */
    #[ORM\ManyToOne(targetEntity: 'InspectionFormData', inversedBy: 'files')]
    #[ORM\JoinColumn(name: 'if_data_id', referencedColumnName: 'id', onDelete: 'cascade', nullable: false)]
    private $formData;

    /**
     * @var string
     */
    #[ORM\Column(name: 'type', type: 'string', length: 255, nullable: true)]
    private $type;

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
     * Set file.
     *
     * @param File $file
     *
     * @return InspectionFormFile
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get file.
     *
     * @return File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getFileData()
    {
        $this->file->setPath(LocalFileService::INSPECTION_FORM_PUBLIC_PATH);
        return $this->file->toArray();
    }

    /**
     * @param $formData
     * @return $this
     */
    public function setFormData($formData)
    {
        $this->formData = $formData;

        return $this;
    }

    /**
     * @return InspectionFormData|mixed
     */
    public function getFormData()
    {
        return $this->formData;
    }

    /**
     * @param $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
