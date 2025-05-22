<?php

namespace App\Entity;

use App\Util\AttributesTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueEntity(
 *     fields={"name"},
 *     message="Name is already exists."
 * )
 */
#[ORM\Table(name: 'device_camera_event_type')]
#[ORM\UniqueConstraint(columns: ['name'])]
#[ORM\Entity(repositoryClass: 'App\Repository\DeviceCameraEventTypeRepository')]
#[ORM\HasLifecycleCallbacks]
class DeviceCameraEventType extends BaseEntity
{
    use AttributesTrait;

    public const UNFASTENED_SEAT_BELT = 'UNFASTENED_SEAT_BELT';
    public const HARSH_CORNERING = 'HARSH_CORNERING';
    public const HARSH_ACCELERATION = 'HARSH_ACCELERATION';
    public const HARSH_BRAKING = 'HARSH_BRAKING';
    public const OVERSPEEDING = 'OVERSPEEDING';

    public const DEFAULT_DISPLAY_VALUES = [
        'id',
        'name',
        'label',
    ];

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    /**
     * @Assert\NotBlank
     */
    #[ORM\Column(name: 'name', type: 'string', nullable: false, unique: true)]
    private string $name;

    /**
     * @Assert\NotBlank
     */
    #[ORM\Column(name: 'label', type: 'string', nullable: false)]
    private string $label;

    /**
     * @param array $fields
     */
    public function __construct(array $fields = [])
    {
        $this->name = $fields['name'] ?? null;
        $this->label = $fields['label'] ?? null;
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
        if (in_array('name', $include, true)) {
            $data['name'] = $this->getName();
        }
        if (in_array('label', $include, true)) {
            $data['label'] = $this->getLabel();
        }

        return $data;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }
}

