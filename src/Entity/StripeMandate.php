<?php

namespace App\Entity;

use App\Repository\StripeMandateRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StripeMandateRepository::class)]
class StripeMandate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $mandateId;

    #[ORM\Column(type: 'string', length: 255)]
    private $paymentMethodId;

    #[ORM\Column(type: 'string', length: 255)]
    private $status;

    #[ORM\Column(type: 'integer')]
    private $acceptedAt;

    public function __construct(array $fields)
    {
        $this->mandateId = $fields['id'];
        $this->paymentMethodId = $fields['payment_method'];
        $this->status = $fields['status'];
        $this->acceptedAt = $fields['customer_acceptance']['accepted_at'];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMandateId(): ?string
    {
        return $this->mandateId;
    }

    public function setMandateId(string $mandateId): self
    {
        $this->mandateId = $mandateId;

        return $this;
    }

    public function getPaymentMethodId(): ?string
    {
        return $this->paymentMethodId;
    }

    public function setPaymentMethodId(string $paymentMethodId): self
    {
        $this->paymentMethodId = $paymentMethodId;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getAcceptedAt(): ?int
    {
        return $this->acceptedAt;
    }

    public function setAcceptedAt(int $acceptedAt): self
    {
        $this->acceptedAt = $acceptedAt;

        return $this;
    }
}
