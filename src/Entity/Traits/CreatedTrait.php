<?php

namespace App\Entity\Traits;

use App\Entity\BaseEntity;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;

use App\Entity\User;

trait CreatedTrait
{
    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $createdBy;

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAtFormatted
     *
     * @return string
     */
    public function getCreatedAtFormatted()
    {
        return $this->createdAt ? Carbon::createFromTimestamp($this->createdAt->getTimestamp())->format(
            BaseEntity::EXPORT_DATE_FORMAT
        ) : null;
    }

    /**
     * @return Carbon|\DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set createdBy
     *
     * @param User $createdBy
     *
     * @return self
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @return string
     */
    public function getCreatedByName(): ?string
    {
        return $this->createdBy ? $this->createdBy->getFullName() : null;
    }
}