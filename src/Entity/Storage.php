<?php

namespace App\Entity;

use App\Util\AttributesTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Storage
 */
#[ORM\Table(name: 'storage')]
#[ORM\Index(name: 'storage_user_id_key_index', columns: ['user_id', 'key'])]
#[ORM\Entity(repositoryClass: 'App\Repository\StorageRepository')]
class Storage extends BaseEntity
{
    use AttributesTrait;

    public function __construct(array $fields)
    {
        $this->key = $fields['key'];
        $this->value = $fields['value'];
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'value' => $this->value,
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
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private $user;

    /**
     * @var string
     */
    #[ORM\Column(name: 'key', type: 'string', length: 255, unique: false)]
    private $key;

    /**
     * @var string
     */
    #[ORM\Column(name: 'value', type: 'text')]
    private $value;


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
     * Set key.
     *
     * @param string $key
     *
     * @return Storage
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set value.
     *
     * @param string $value
     *
     * @return Storage
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return User
     */
    public function getUser()
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
}
