<?php

namespace App\Entity;

use App\Repository\UserTermAcceptanceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'user_terms_acceptance')]
#[ORM\Entity(repositoryClass: 'App\Repository\UserTermAcceptanceRepository')]
class UserTermAcceptance extends BaseEntity
{
    public function __construct(array $fields)
    {
        $this->user = $fields['user'] ?? null;
        $this->type = $fields['type'] ?? null;
        $this->createdAt = $fields['createdAt'] ?? null;
    }

    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->id;
        $data['type'] = $this->getType();
        $data['user'] = $this->getUser()->toArray(User::SIMPLE_VALUES);
        $data['createdAt'] = $this->formatDate($this->getCreatedAt());

        return $data;
    }

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
    private $user;

    /**
     * @var string
     */
    #[ORM\Column(name: 'type', type: 'string', length: 100)]
    private $type;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setCreatedAt($createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
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

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }
}
