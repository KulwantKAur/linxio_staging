<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Sms
 */
#[ORM\Table(name: 'sms')]
#[ORM\Entity(repositoryClass: 'App\Repository\SmsRepository')]
#[ORM\HasLifecycleCallbacks]
class Sms extends BaseEntity
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'phone_from', type: 'string', length: 20)]
    private $phoneFrom;

    /**
     * @var string
     */
    #[ORM\Column(name: 'phone_to', type: 'string', length: 20)]
    private $phoneTo;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true)]
    private $user;

    /**
     * @var string
     */
    #[ORM\Column(name: 'status', type: 'string', length: 255)]
    private $status;

    /**
     * @var string
     */
    #[ORM\Column(name: 'message', type: 'text', nullable: true)]
    private $message;

    /**
     * @var string
     */
    #[ORM\Column(name: 'message_uuid', type: 'string', length: 255, nullable: true, unique: true)]
    private $messageUuid;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private $updatedAt;

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
     * Set phoneFrom
     *
     * @param string $phoneFrom
     *
     * @return Sms
     */
    public function setPhoneFrom($phoneFrom)
    {
        $this->phoneFrom = $phoneFrom;

        return $this;
    }

    /**
     * Get phoneFrom
     *
     * @return string
     */
    public function getPhoneFrom()
    {
        return $this->phoneFrom;
    }

    /**
     * Set phoneTo
     *
     * @param string $phoneTo
     *
     * @return Sms
     */
    public function setPhoneTo($phoneTo)
    {
        $this->phoneTo = $phoneTo;

        return $this;
    }

    /**
     * Get phoneTo
     *
     * @return string
     */
    public function getPhoneTo()
    {
        return $this->phoneTo;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Sms
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set message
     *
     * @param string $message
     *
     * @return Sms
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set messageUuid
     *
     * @param string $messageUuid
     *
     * @return Sms
     */
    public function setMessageUuid($messageUuid)
    {
        $this->messageUuid = $messageUuid;

        return $this;
    }

    /**
     * Get messageUuid
     *
     * @return string
     */
    public function getMessageUuid()
    {
        return $this->messageUuid;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Sms
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Sms
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    #[ORM\PreUpdate]
    public function updatedTimestamps(): void
    {
        $this->setUpdatedAt(new \DateTime());
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
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'phoneFrom' => $this->getPhoneFrom(),
            'phoneTo' => $this->getPhoneTo(),
            'status' => $this->getStatus(),
            'createdAt' => $this->formatDate($this->getCreatedAt()),
            'updatedAt' => $this->formatDate($this->getUpdatedAt()),
        ];
    }
}

