<?php

namespace App\Entity\Xero;

use App\Entity\BaseEntity;
use App\Entity\Client;
use App\Entity\Team;
use App\Entity\User;
use App\Repository\Xero\XeroClientAccountRepository;
use App\Util\AttributesTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'xero_client_account')]
#[ORM\Entity(repositoryClass: XeroClientAccountRepository::class)]
class XeroClientAccount extends BaseEntity
{
    use AttributesTrait;

    public const DEFAULT_DISPLAY_VALUES = [
        'team',
        'xeroContactId',
        'client',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $xeroContactId;

    /**
     * @var Team
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Team', inversedBy: 'xeroClientAccount')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id')]
    private $team;

    /**
     * @var Client
     */
    #[ORM\OneToOne(inversedBy: 'xeroClientAccount', targetEntity: 'App\Entity\Client')]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'id')]
    private $client;

    public function __construct(array $fields = [])
    {
        $this->team = $fields['team'] ?? null;
        $this->xeroContactId = $fields['contactId'] ?? null;
        $this->client = $fields['client'] ?? null;
    }

    public function toArray(): array
    {
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('xeroContactId', $include, true)) {
            $data['xeroContactId'] = $this->getXeroContactId();
        }
        if (in_array('team', $include, true)) {
            $data['team'] = $this->team ? $this->getTeam()->toArray() : null;
        }
        if (in_array('client', $include, true)) {
            $data['client'] = $this->client ? $this->getClient()->toArray() : null;
        }

        return $data;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getXeroContactId(): ?string
    {
        return $this->xeroContactId;
    }

    public function setXeroContactId(string $xeroContactId): self
    {
        $this->xeroContactId = $xeroContactId;

        return $this;
    }

    /**
     * Set team.
     *
     * @param Team $team
     *
     * @return XeroClientAccount
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
     * Set client.
     *
     * @param Client $client
     *
     * @return XeroClientAccount
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client.
     *
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }
}
