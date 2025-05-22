<?php

namespace App\Entity\Notification;

use App\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * AcknowledgeRecipients
 */
#[ORM\Table(name: 'acknowledge_recipients')]
#[ORM\Entity(repositoryClass: 'App\Repository\Notification\AcknowledgeRecipientsRepository')]
class AcknowledgeRecipients extends BaseEntity
{
    public const TYPE_ROLE = 'role';
    public const TYPE_USERS_LIST = 'users_list';
    public const TYPE_USER_GROUPS_LIST = 'user_groups_list';
    public const TYPE_SELF = 'self';

    public const DISPLAY_TYPES = [
        self::TYPE_ROLE,
        self::TYPE_USERS_LIST,
        self::TYPE_USER_GROUPS_LIST,
    ];

    public const DEFAULT_DISPLAY_VALUES = [
        'id',
        'type',
        'value',
    ];

    public function toArray(array $include = []): array
    {
        $data = [];

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('id', $include, true)) {
            $data['id'] = $this->id;
        }

        if (in_array('type', $include, true)) {
            $data['type'] = $this->type;
        }

        if (in_array('value', $include, true)) {
            $data['value'] = $this->value;
        }

        return $data;
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
    #[ORM\Column(name: 'type', type: 'string')]
    private $type;

    /**
     * @var array
     */
    #[ORM\Column(name: 'value', type: 'json', nullable: true)]
    private $value;

    /**
     * @var Notification
     */
    #[ORM\ManyToOne(targetEntity: 'Notification', inversedBy: 'acknowledgeRecipients')]
    #[ORM\JoinColumn(name: 'notification_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private $notification;


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
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Set value.
     *
     * @param array $value
     *
     * @return AcknowledgeRecipients
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return array
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return Notification
     */
    public function getNotification(): Notification
    {
        return $this->notification;
    }

    /**
     * @param Notification $notification
     * @return $this
     */
    public function setNotification(Notification $notification)
    {
        $this->notification = $notification;

        return $this;
    }
}
