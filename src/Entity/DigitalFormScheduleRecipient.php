<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DigitalFormScheduleRecipient
 */
#[ORM\Table(name: 'digital_form_schedule_recipient')]
#[ORM\Entity(repositoryClass: 'App\Repository\DigitalFormScheduleRecipientRepository')]
class DigitalFormScheduleRecipient extends BaseEntity
{
    /** @var string */
    public const TYPE_ANY = 'any';
    public const TYPE_AREA = 'area';
    public const TYPE_DEPOT = 'depot';
    public const TYPE_GROUP = 'group';
    public const TYPE_VEHICLE = 'vehicle';

    /** @var string */
    public const ADDITIONAL_TYPE_AREA = 'area';

    /** @var array */
    public const VALID_TYPES = [
        self::TYPE_ANY,
        self::TYPE_AREA,
        self::TYPE_DEPOT,
        self::TYPE_GROUP,
        self::TYPE_VEHICLE,
    ];

    /** @var array */
    public const VALID_ADDITIONAL_TYPES = [
        self::ADDITIONAL_TYPE_AREA,
    ];

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var DigitalFormSchedule
     */
    #[ORM\ManyToOne(targetEntity: 'DigitalFormSchedule', inversedBy: 'digitalFormScheduleRecipients')]
    #[ORM\JoinColumn(name: 'digital_form_schedule_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: false)]
    private $digitalFormSchedule;

    /**
     * @var string
     */
    #[ORM\Column(name: 'type', type: 'string', length: 64)]
    private $type;

    /**
     * @var array
     */
    #[ORM\Column(name: 'value', type: 'json', nullable: false, options: ['jsonb' => true])]
    private $value;

    /**
     * @var string
     */
    #[ORM\Column(name: 'additional_type', nullable: true, type: 'string', length: 64)]
    private $additionalType;

    /**
     * @var array
     */
    #[ORM\Column(name: 'additional_value', type: 'json', nullable: true, options: ['jsonb' => true])]
    private $additionalValue;


    public function getId(): int
    {
        return $this->id;
    }

    public function setDigitalFormSchedule(DigitalFormSchedule $digitalFormSchedule): self
    {
        $this->digitalFormSchedule = $digitalFormSchedule;

        return $this;
    }

    public function getDigitalFormSchedule(): DigitalFormSchedule
    {
        return $this->digitalFormSchedule;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setValue(array $value): self
    {
        $this->value = [];
        foreach ($value as $id) {
            $this->value[] = (int)$id;
        }

        return $this;
    }

    public function getValue(): array
    {
        return $this->value;
    }

    public function setAdditionalType(string $additionalType): self
    {
        $this->additionalType = $additionalType;

        return $this;
    }

    public function getAdditionalType(): ?string
    {
        return $this->additionalType;
    }

    public function setAdditionalValue(array $additionalValue): self
    {
        $this->additionalValue = [];
        foreach ($additionalValue as $id) {
            $this->additionalValue[] = (int)$id;
        }

        return $this;
    }

    public function getAdditionalValue(): ?array
    {
        return $this->additionalValue;
    }

    /**
     * @inheritDoc
     */
    public function toArray(array $include = []): array
    {
        $data = [
            'id' => $this->getId(),
            'type' => $this->getType(),
            'value' => $this->getValue(),
            'additionalType' => $this->getAdditionalType(),
            'additionalValue' => $this->getAdditionalValue(),
        ];

        return $data;
    }
}
