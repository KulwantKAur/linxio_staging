<?php

namespace App\Entity;

use App\Util\StringHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * EntityHistory
 */
#[ORM\Table(name: 'entity_history')]
#[ORM\Index(name: 'entity_history_entity_id_type_created_at_index', columns: ['entity_id', 'type', 'created_at'])]
#[ORM\Index(name: 'entity_history_entity_id_type_entity_index', columns: ['entity_id', 'type', 'entity'])]
#[ORM\Entity(repositoryClass: 'App\Repository\EntityHistoryRepository')]
class EntityHistory extends BaseEntity
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'entity', type: 'string', length: 255)]
    private $entity;

    /**
     * @var int
     */
    #[ORM\Column(name: 'entity_id', type: 'integer', nullable: true)]
    private $entityId;

    /**
     * @var string
     */
    #[ORM\Column(name: 'payload', type: 'text')]
    private $payload;

    /**
     * @var User
     *
     * @Assert\NotBlank
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private $createdBy;

    /**
     * @var string
     *
     *
     * @Assert\Email
     */
    #[ORM\Column(name: 'email', type: 'string', length: 255, nullable: true)]
    private $email;

    /**
     * @var string
     *
     *
     * @Assert\Choice(callback={"App\Enums\EntityHistoryTypes", "getAll"})
     */
    #[ORM\Column(name: 'type', type: 'string', length: 255)]
    private $type;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
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
     * Set entity
     *
     * @param string $entity
     *
     * @return EntityHistory
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get entity
     *
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set entityId
     *
     * @param integer $entityId
     *
     * @return EntityHistory
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * Get entityId
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * Set payload
     *
     * @param string $payload
     *
     * @return EntityHistory
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;

        return $this;
    }

    /**
     * Get payload
     *
     * @return string
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return EntityHistory
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return EntityHistory
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
     * @return User|null
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param $createdBy
     */
    public function setCreatedBy($createdBy = null): void
    {
        if (!($createdBy instanceof User)) {
            $createdBy = null;
        }

        $this->createdBy = $createdBy;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'createdByName' => $this->getCreatedBy() ? $this->getCreatedBy()->getFullName() : null,
            'payload' => StringHelper::isJson($this->getPayload())
                ? json_decode($this->getPayload()) : $this->getPayload(),
            'createdAt' => $this->formatDate($this->getCreatedAt()),
            'type' => $this->getType(),
        ];
    }

    public static function preparePayload(mixed $value): string
    {
        if ($value instanceof \DateTime) {
            return $value->format('c');
        } elseif ($value instanceof DeviceModel) {
            return $value->getName();
        } elseif ($value instanceof DeviceVendor) {
            return $value->getName();
        } elseif (is_string($value) || is_numeric($value)) {
            return (string)$value;
        } elseif (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return '';
    }
}

