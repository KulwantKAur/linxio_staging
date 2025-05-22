<?php

namespace App\Entity\Tracker;

use App\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'tracker_payload_streamax')]
#[ORM\Index(columns: ['created_at'], name: 'tracker_payload_streamax_created_at_idx')]
#[ORM\Entity(repositoryClass: 'App\Repository\Tracker\TrackerPayloadStreamaxRepository')]
class TrackerPayloadStreamax extends BaseEntity
{
    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(name: 'payload', type: 'text')]
    private string $payload;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private \DateTime $createdAt;

    #[ORM\Column(name: 'is_processed', type: 'boolean', options: ['default' => '0'])]
    private bool $isProcessed = false;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
    }

    public function getId()
    {
        return $this->id;
    }

    public function setPayload($payload)
    {
        $this->payload = $payload;

        return $this;
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'createdAt' => $this->formatDate($this->getCreatedAt()),
            'isProcessed' => $this->isProcessed(),
        ];
    }

    public function isProcessed(): bool
    {
        return $this->isProcessed;
    }

    public function setIsProcessed(bool $isProcessed): void
    {
        $this->isProcessed = $isProcessed;
    }
}
