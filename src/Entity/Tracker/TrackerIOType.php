<?php

namespace App\Entity\Tracker;

use App\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * TrackerIOType
 */
#[ORM\Table(name: 'tracker_io_type')]
#[ORM\Index(name: 'tracker_io_type_name_index', columns: ['name'])]
#[ORM\Entity(repositoryClass: 'App\Repository\Tracker\TrackerIOTypeRepository')]
class TrackerIOType extends BaseEntity
{
    public const AC_DIGITAL_INPUT = 'ACDigitalInput';
    public const IGNITION_INPUT = 'ignitionInput';
    public const DIGITAL_INPUT_1 = 'digitalInput1';
    public const DIGITAL_INPUT_2 = 'digitalInput2';
    public const DIGITAL_INPUT_3 = 'digitalInput3';
    public const DIGITAL_INPUT_4 = 'digitalInput4';
    public const DIGITAL_INPUT_5 = 'digitalInput5';

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'name', type: 'string', nullable: false)]
    private $name;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'label', type: 'string', length: 255, nullable: true)]
    private $label;

    /**
     * @var int
     */
    #[ORM\Column(name: 'sort', type: 'smallint', nullable: false, options: ['default' => 0])]
    private int $order = 0;

    public function __toString()
    {
        return strval($this->getId());
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'label' => $this->getLabel(),
        ];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @param string|null $label
     * @return self
     */
    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return $this->order;
    }

    /**
     * @param int $order
     */
    public function setOrder(int $order): void
    {
        $this->order = $order;
    }

    /**
     * @return array
     */
    public static function getAllTypeNames(): array
    {
        return [
            self::AC_DIGITAL_INPUT,
            self::IGNITION_INPUT,
            self::DIGITAL_INPUT_1,
            self::DIGITAL_INPUT_2,
            self::DIGITAL_INPUT_3,
            self::DIGITAL_INPUT_4,
            self::DIGITAL_INPUT_5,
        ];
    }
}
