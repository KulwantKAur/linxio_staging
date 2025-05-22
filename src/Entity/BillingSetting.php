<?php

namespace App\Entity;

use App\Util\AttributesTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\BillingSettingRepository')]
class BillingSetting extends BaseEntity
{
    use AttributesTrait;

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

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
    private string $accountName;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private string $bsb;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private string $accountNumber;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private string $swiftCode;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isStripeEnabled;

    public function __construct(array $fields = [])
    {
        $this->team = $fields['team'] ?? null;
        $this->accountName = $fields['accountName'] ?? null;
        $this->bsb = $fields['bsb'] ?? null;
        $this->accountNumber = $fields['accountNumber'] ?? null;
        $this->swiftCode = $fields['swiftCode'] ?? null;
        $this->isStripeEnabled = $fields['isStripeEnabled'] ?? false;
    }

    public function toArray(): array
    {
        $data = [];
        $data['accountName'] = $this->accountName;
        $data['bsb'] = $this->bsb;
        $data['accountNumber'] = $this->accountNumber;
        $data['swiftCode'] = $this->swiftCode;
        $data['isStripeEnabled'] = $this->getIsStripeEnabled();
//        $data['team'] = $this->team->toArray();

        return $data;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAccountName(): ?string
    {
        return $this->accountName;
    }

    public function setAccountName(string $name): self
    {
        $this->accountName = $name;

        return $this;
    }

    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(string $number): self
    {
        $this->accountNumber = $number;

        return $this;
    }

    public function getBsb(): ?string
    {
        return $this->bsb;
    }

    public function setBsb(string $bsb): self
    {
        $this->bsb = $bsb;

        return $this;
    }

    public function getSwiftCode(): ?string
    {
        return $this->swiftCode;
    }

    public function setSwiftCode(string $swiftCode): self
    {
        $this->swiftCode = $swiftCode;

        return $this;
    }

    public function setTeam(Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }

    public function getIsStripeEnabled(): bool
    {
        return $this->isStripeEnabled;
    }

    public function setIsStripeEnabled(bool $isStripeEnabled): ?self
    {
        $this->isStripeEnabled = $isStripeEnabled;

        return $this;
    }
}
