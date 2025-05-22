<?php

namespace App\Entity\Xero;

use App\Entity\BaseEntity;
use App\Entity\Team;
use App\Repository\Xero\XeroClientSecretRepository;
use App\Util\AttributesTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'xero_secret')]
#[ORM\Entity(repositoryClass: XeroClientSecretRepository::class)]
class XeroClientSecret extends BaseEntity
{
    use AttributesTrait;

    public const DEFAULT_DISPLAY_VALUES = [
        'team',
        'xeroClientId',
        'xeroClientSecret',
        'xeroTenantId',
        'xeroAccountPaymentId',
        'xeroAccountLineitemId',
    ];

    public const EDITABLE_FIELDS = [
        'xeroTenantId',
        'xeroAccountPaymentId',
        'xeroAccountLineitemId',
        'xeroClientSecret',
        'xeroClientId',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $xeroClientId;

    #[ORM\Column(type: 'string', length: 255)]
    private $xeroClientSecret;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $xeroTenantId;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $xeroAccountPaymentId;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $xeroAccountLineitemId;

    /**
     * @var Team
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Team', inversedBy: 'xeroClientSecret')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id')]
    private $team;

    public function __construct(array $fields)
    {
        $this->team = $fields['team'] ?? null;
        $this->xeroClientId = $fields['xeroClientId'];
        $this->xeroClientSecret = $fields['xeroClientSecret'];
        $this->xeroTenantId = $fields['tenant_id'] ?? null;
    }

    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('xeroClientId', $include, true)) {
            $data['xeroClientId'] = $this->getXeroClientId();
        }
        if (in_array('xeroClientSecret', $include, true)) {
            $data['xeroClientSecret'] = $this->getXeroClientSecret();
        }
        if (in_array('xeroTenantId', $include, true)) {
            $data['xeroTenantId'] = $this->getXeroTenantId();
        }
        if (in_array('xeroAccountPaymentId', $include, true)) {
            $data['xeroAccountPaymentId'] = $this->getXeroAccountPaymentId();
        }
        if (in_array('xeroAccountLineitemId', $include, true)) {
            $data['xeroAccountLineitemId'] = $this->getXeroAccountLineitemId();
        }
        if (in_array('team', $include, true)) {
            $data['team'] = $this->team ? $this->getTeam()->toArray() : null;
        }

        return $data;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getXeroClientId(): ?string
    {
        return $this->xeroClientId;
    }

    public function setXeroClientId(string $xeroClientId): self
    {
        $this->xeroClientId = $xeroClientId;

        return $this;
    }

    public function getXeroClientSecret(): ?string
    {
        return $this->xeroClientSecret;
    }

    public function setXeroClientSecret(string $xeroClientSecret): self
    {
        $this->xeroClientSecret = $xeroClientSecret;

        return $this;
    }

    /**
     * Set team.
     *
     * @param Team $team
     *
     * @return XeroClientSecret
     */
    public function setTeam(Team $team)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * Get team.
     *
     * @return Team
     */
    public function getTeam(): Team
    {
        return $this->team;
    }

    /**
     * @return string
     */
    public function getXeroTenantId(): ?string
    {
        return $this->xeroTenantId;
    }

    /**
     * @param string $xeroTenantId
     */
    public function setXeroTenantId(string $xeroTenantId): void
    {
        $this->xeroTenantId = $xeroTenantId;
    }

    /**
     * @return string|null
     */
    public function getXeroAccountPaymentId(): ?string
    {
        return $this->xeroAccountPaymentId;
    }

    /**
     * @param string $xeroAccountPaymentId
     */
    public function setXeroAccountPaymentId($xeroAccountPaymentId): void
    {
        $this->xeroAccountPaymentId = $xeroAccountPaymentId;
    }

    /**
     * @return string|null
     */
    public function getXeroAccountLineitemId(): ?string
    {
        return $this->xeroAccountLineitemId;
    }

    /**
     * @param string $xeroAccountLineitemId
     */
    public function setXeroAccountLineitemId($xeroAccountLineitemId): void
    {
        $this->xeroAccountLineitemId = $xeroAccountLineitemId;
    }
}
