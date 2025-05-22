<?php

namespace App\Entity;

use App\Util\AttributesTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * UserDevice
 */
#[ORM\Table(name: 'user_device')]
#[ORM\Entity(repositoryClass: 'App\Repository\UserDeviceRepository')]
class UserDevice extends BaseEntity
{
    use AttributesTrait;

    /**
     * UserDevice constructor.
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->deviceId = $fields['deviceId'];
        $this->user = $fields['user'];
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'deviceId' => $this->getDeviceId()
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
     * @var string|null
     */
    #[ORM\Column(name: 'deviceId', type: 'text', nullable: true, unique: true)]
    private $deviceId;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'User', inversedBy: 'userDevices')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private $user;


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set deviceId.
     *
     * @param string|null $deviceId
     *
     * @return UserDevice
     */
    public function setDeviceId($deviceId = null)
    {
        $this->deviceId = $deviceId;

        return $this;
    }

    /**
     * Get deviceId.
     *
     * @return string|null
     */
    public function getDeviceId()
    {
        return $this->deviceId;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get user
     *
     * @param User $user
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }
}
