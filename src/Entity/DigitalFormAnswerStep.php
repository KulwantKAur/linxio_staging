<?php

declare(strict_types=1);

namespace App\Entity;

use App\Service\File\LocalFileService;
use App\Util\DateHelper;
use Doctrine\ORM\Mapping as ORM;

/**
 * DigitalFormAnswerStep
 */
#[ORM\Table(name: 'digital_form_answer_step')]
#[ORM\Entity(repositoryClass: 'App\Repository\DigitalFormAnswerStepRepository')]
class DigitalFormAnswerStep extends BaseEntity
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var DigitalFormAnswer
     */
    #[ORM\ManyToOne(targetEntity: 'DigitalFormAnswer', inversedBy: 'digitalFormAnswerSteps')]
    #[ORM\JoinColumn(name: 'digital_form_answer_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: false)]
    private $digitalFormAnswer;

    /**
     * @var DigitalFormStep
     */
    #[ORM\ManyToOne(targetEntity: 'DigitalFormStep')]
    #[ORM\JoinColumn(name: 'digital_form_step_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: false)]
    private $digitalFormStep;

    /**
     * @var File
     */
    #[ORM\ManyToOne(targetEntity: 'File')]
    #[ORM\JoinColumn(name: 'file_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $file;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'is_pass', type: 'boolean', nullable: true)]
    private $isPass;

    /**
     * @var json
     */
    #[ORM\Column(name: 'value', type: 'json', nullable: true, options: ['jsonb' => true])]
    private $value;

    /**
     * @var int
     */
    #[ORM\Column(name: 'duration', type: 'integer', nullable: false, options: ['default' => 0])]
    private $duration = 0;

    /**
     * @var string
     */
    #[ORM\Column(name: 'additional_note', type: 'string', length: 8096, nullable: true)]
    private $additionalNote;

    /**
     * @var File
     */
    #[ORM\ManyToOne(targetEntity: 'File')]
    #[ORM\JoinColumn(name: 'additional_file_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $additionalFile;

    public function getId(): int
    {
        return (int)$this->id;
    }

    public function setDigitalFormAnswer(DigitalFormAnswer $digitalFormAnswer): self
    {
        $this->digitalFormAnswer = $digitalFormAnswer;

        return $this;
    }

    public function getDigitalFormAnswer(): DigitalFormAnswer
    {
        return $this->digitalFormAnswer;
    }

    public function setDigitalFormStep(DigitalFormStep $digitalFormStep): self
    {
        $this->digitalFormStep = $digitalFormStep;

        return $this;
    }

    public function getDigitalFormStep(): DigitalFormStep
    {
        return $this->digitalFormStep;
    }

    public function setFile(File $file = null): self
    {
        $this->file = $file;

        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setValue($value = null): self
    {
        // cast index values into INT
        if (is_array($value)) {
            foreach ($value as $key => $index) {
                if (is_numeric($index)) {
                    $value[$key] = (int)$index;
                }
            }
        }

        $this->value = $value;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setIsPass(bool $isPass): self
    {
        $this->isPass = $isPass;

        return $this;
    }

    public function getIsPass(): ?bool
    {
        return $this->isPass;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setAdditionalNote(string $additionalNote): self
    {
        $this->additionalNote = $additionalNote;

        return $this;
    }

    public function getAdditionalNote(): ?string
    {
        return $this->additionalNote;
    }

    public function setAdditionalFile(File $additionalFile = null): self
    {
        $this->additionalFile = $additionalFile;

        return $this;
    }

    public function getAdditionalFile(): ?File
    {
        return $this->additionalFile;
    }

    /**
     * @inheritDoc
     */
    public function toArray(array $include = []): array
    {
        $data = [
            'id' => $this->getId(),
            'value' => $this->getValue(),
            'isPass' => $this->getIsPass(),
            'duration' => $this->getDuration(),
            'additionalNote' => $this->getAdditionalNote(),
        ];

        if (in_array('additionalFile', $include, true)) {
            $data['additionalFile'] = $this->getAdditionalFileData();

        }
        if (in_array('file', $include, true)) {
            $data['file'] = $this->getFileData();
        }
        if (in_array('steps', $include, true)) {
            $data['step'] = $this->getDigitalFormStep()->toArray();
        }

        return $data;
    }

    public function getFileData(): ?array
    {
        if ($this->getFile() !== null) {
            $this->getFile()->setPath(LocalFileService::DIGITAL_FORM_PUBLIC_PATH);
            return [
                'id' => $this->getFile()->getId(),
                'name' => $this->getFile()->getName(),
                'path' => $this->getFile()->getPath(),
                'displayName' => $this->getFile()->getDisplayName(),
            ];
        }

        return null;
    }

    public function getAdditionalFileData(): ?array
    {
        if ($this->getAdditionalFile() !== null) {
            $this->getAdditionalFile()->setPath(LocalFileService::DIGITAL_FORM_PUBLIC_PATH);
            return [
                'id' => $this->getAdditionalFile()->getId(),
                'name' => $this->getAdditionalFile()->getName(),
                'path' => $this->getAdditionalFile()->getPath(),
                'displayName' => $this->getAdditionalFile()->getDisplayName(),
            ];
        }

        return null;
    }

    public function getDurationHuman(): ?string
    {
        return DateHelper::seconds2human($this->getDuration());
    }
}
