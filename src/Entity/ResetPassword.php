<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ResetPassword
 */
#[ORM\Table(name: 'reset_password')]
#[ORM\Entity(repositoryClass: 'App\Repository\ResetPasswordRepository')]
class ResetPassword
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
    #[ORM\Column(name: 'email', type: 'string', length: 255, nullable: true)]
    private $email;

    /**
     * @var string
     */
    #[ORM\Column(name: 'phone', type: 'string', length: 20, nullable: true)]
    private $phone;

    /**
     * @var User
     */
    #[ORM\OneToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true)]
    private $user;

    /**
     * @var string
     */
    #[ORM\Column(name: 'token', type: 'string', length: 255, unique: true)]
    private $token;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'verified_at', type: 'datetime', nullable: true)]
    private $verifiedAt;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * ResetPassword constructor.
     */
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
     * Set email
     *
     * @param string $email
     *
     * @return ResetPassword
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return ResetPassword
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
     * Set userId
     *
     * @param User $user
     *
     * @return ResetPassword
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get userId
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set token
     *
     * @param string $token
     *
     * @return ResetPassword
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
     * Set verifiedAt
     *
     * @param \DateTime $verifiedAt
     *
     * @return ResetPassword
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ResetPassword
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
}

