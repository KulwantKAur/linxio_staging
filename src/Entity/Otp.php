<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Otp
 */
#[ORM\Table(name: 'otp')]
#[ORM\Entity(repositoryClass: 'App\Repository\OtpRepository')]
class Otp extends BaseEntity
{
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
    #[ORM\Column(name: 'phone', type: 'string', length: 20, nullable: true)]
    private $phone;

    /**
     * @var string
     */
    #[ORM\Column(name: 'email', type: 'string', length: 255, nullable: true)]
    private $email;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true)]
    private $user;

    /**
     * @var string
     */
    #[ORM\Column(name: 'code', type: 'string', length: 20)]
    private $code;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'verified_at', type: 'datetime', nullable: true)]
    private $verifiedAt;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
    }

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
     * Set phone
     *
     * @param string $phone
     *
     * @return Otp
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return Otp
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Otp
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set verifiedAt
     *
     * @param \DateTime $verifiedAt
     *
     * @return Otp
     */
    public function setVerifiedAt($verifiedAt)
    {
        $this->verifiedAt = $verifiedAt;

        return $this;
    }

    /**
     * Get verifiedAt
     *
     * @return \DateTime
     */
    public function getVerifiedAt()
    {
        return $this->verifiedAt;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
        ];
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }
}

