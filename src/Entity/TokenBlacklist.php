<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TokenBlacklist
 */
#[ORM\Table(name: 'token_blacklist')]
#[ORM\Entity(repositoryClass: 'App\Repository\TokenBlacklistRepository')]
class TokenBlacklist extends BaseEntity
{
    /**
     * TokenBlacklist constructor.
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->setToken($fields['token']);
        $this->setExpiredAt($fields['expiredAt']);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'token' => $this->getToken(),
            'expiredAt' => $this->getExpiredAt()
        ];
    }

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'token', type: 'text')]
    private $token;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'expired_at', type: 'datetime')]
    private $expiredAt;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set token
     *
     * @param string $token
     *
     * @return TokenBlacklist
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set expiredAt
     *
     * @param User $expiredAt
     *
     * @return TokenBlacklist
     */
    public function setExpiredAt($expiredAt)
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }

    /**
     * Get expiredAt
     *
     * @return \DateTime
     */
    public function getExpiredAt(): \DateTime
    {
        return $this->expiredAt;
    }
}

