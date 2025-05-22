<?php

namespace App\Entity;

use App\Util\AttributesTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\AdminTeamInfoRepository')]
class AdminTeamInfo extends BaseEntity
{
    use AttributesTrait;

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var Team
     */
    #[ORM\ManyToOne(targetEntity: 'Team', inversedBy: 'vehicles')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id')]
    private $team;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private string $companyName;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private string $legalName;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private string $abn;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private string $legalAddress;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private string $billingAddress;

    public function __construct(array $fields = [])
    {
        $this->team = $fields['team'] ?? null;
        $this->companyName = $fields['companyName'] ?? null;
        $this->legalName = $fields['legalName'] ?? null;
        $this->abn = $fields['abn'] ?? null;
        $this->legalAddress = $fields['legalAddress'] ?? null;
        $this->billingAddress = $fields['billingAddress'] ?? null;
    }

    public function toArray(): array
    {
        $data = [];
        $data['companyName'] = $this->companyName;
        $data['legalName'] = $this->legalName;
        $data['abn'] = $this->abn;
        $data['legalAddress'] = $this->legalAddress;
        $data['billingAddress'] = $this->billingAddress;

        return $data;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $name): self
    {
        $this->companyName = $name;

        return $this;
    }

    public function getLegalName(): ?string
    {
        return $this->legalName;
    }

    public function setLegalName(string $name): self
    {
        $this->legalName = $name;

        return $this;
    }

    public function getAbn(): ?string
    {
        return $this->abn;
    }

    public function setAbn(string $abn): self
    {
        $this->abn = $abn;

        return $this;
    }

    public function getLegalAddress(): ?string
    {
        return $this->legalAddress;
    }

    public function setLegalAddress(string $address): self
    {
        $this->legalAddress = $address;

        return $this;
    }

    public function getBillingAddress(): ?string
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(string $address): self
    {
        $this->billingAddress = $address;

        return $this;
    }

    public function setTeam(Team $team): self
    {
        $this->team = $team;

        return $this;
    }
}
