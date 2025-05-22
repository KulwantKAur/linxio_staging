<?php

namespace App\Entity;

use App\Repository\StripeSecretRepository;
use App\Util\AttributesTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StripeSecretRepository::class)]
class StripeSecret
{
    public const FEE_REGION = 'au';

    use AttributesTrait;

    public const EDITABLE_FIELDS = [
        'secretKey',
        'publicKey',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: 'Team', inversedBy: 'stripeSecret')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id')]
    private $team;

    #[ORM\Column(type: 'string', length: 255)]
    private $secretKey;

    #[ORM\Column(type: 'string', length: 255)]
    private $publicKey;

    #[ORM\Column(type: 'string', length: 255, options: ['default' => self::FEE_REGION])]
    private $feeRegion = self::FEE_REGION;

    public function __construct(array $fields)
    {
        $this->team = $fields['team'] ?? null;
        $this->secretKey = $fields['secretKey'];
        $this->publicKey = $fields['publicKey'];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    public function getSecretKey(): ?string
    {
        return $this->secretKey;
    }

    public function setSecretKey(string $secretKey): self
    {
        $this->secretKey = $secretKey;

        return $this;
    }

    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    public function setPublicKey(string $publicKey): self
    {
        $this->publicKey = $publicKey;

        return $this;
    }

    public function getFeeRegion(): ?string
    {
        return $this->feeRegion;
    }

    public function setFeeRegion(string $feeRegion): self
    {
        $this->feeRegion = $feeRegion;

        return $this;
    }
}
