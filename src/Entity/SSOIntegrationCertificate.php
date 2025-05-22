<?php

namespace App\Entity;

use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueEntity(
 *     fields={"integrationData", "certificate"},
 *     message="integration certificate already exists."
 * )
 */
#[ORM\Table(name: 'sso_integration_certificate')]
#[ORM\UniqueConstraint(columns: ['integration_data_id', 'certificate'])]
#[ORM\Entity(repositoryClass: 'App\Repository\SSOIntegrationCertificateRepository')]
class SSOIntegrationCertificate extends BaseEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    /**
     * @var SSOIntegrationData
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\SSOIntegrationData', inversedBy: 'certificates')]
    #[ORM\JoinColumn(name: 'integration_data_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SSOIntegrationData $integrationData;

    /**
     * @var string
     *
     * @Assert\NotBlank
     */
    #[ORM\Column(name: 'certificate', type: 'text', nullable: false)]
    private string $certificate;

    /**
     * @var string
     */
    #[ORM\Column(name: 'status', type: 'string', length: 255, options: ['default' => self::STATUS_ENABLED])]
    private string $status;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private \DateTime $createdAt;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private ?\DateTime $updatedAt;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'expired_at', type: 'datetime', nullable: true)]
    private ?\DateTime $expiredAt;

    public function __construct(array $fields)
    {
        $this->certificate = $fields['certificate'];
        $this->status = $fields['status'] ?? self::STATUS_ENABLED;
        $this->updatedAt = $fields['updatedAt'] ?? null;
        $this->expiredAt = $fields['expiredAt'] ?? null;
        $this->createdAt = $fields['createdAt'] ?? new Carbon();
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
        $data['integrationDataId'] = $this->getIntegrationData()->getId();
        $data['certificate'] = $this->getCertificate();
        $data['status'] = $this->getStatus();
        $data['updatedAt'] = $this->formatDate($this->getUpdatedAt());
        $data['expiredAt'] = $this->formatDate($this->getExpiredAt());
        $data['createdAt'] = $this->formatDate($this->getCreatedAt());

        return $data;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function isEnabled()
    {
        return $this->getStatus() === self::STATUS_ENABLED;
    }

    public function isDisabled()
    {
        return $this->getStatus() === self::STATUS_DISABLED;
    }

    /**
     * @param \DateTime|null $updatedAt
     *
     * @return self
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return string
     */
    public function getCertificate(): string
    {
        return $this->certificate;
    }

    /**
     * @param string $certificate
     */
    public function setCertificate(string $certificate): void
    {
        $this->certificate = $certificate;
    }

    /**
     * @return SSOIntegrationData
     */
    public function getIntegrationData(): SSOIntegrationData
    {
        return $this->integrationData;
    }

    /**
     * @param SSOIntegrationData $integrationData
     */
    public function setIntegrationData(SSOIntegrationData $integrationData): void
    {
        $this->integrationData = $integrationData;
    }

    /**
     * @return \DateTime|null
     */
    public function getExpiredAt(): ?\DateTime
    {
        return $this->expiredAt;
    }

    /**
     * @param \DateTime|null $expiredAt
     */
    public function setExpiredAt(?\DateTime $expiredAt): void
    {
        $this->expiredAt = $expiredAt;
    }
}

