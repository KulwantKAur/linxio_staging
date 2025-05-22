<?php

namespace App\Entity\Notification;

use App\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * NotificationScopes
 */
#[ORM\Table(name: 'notification_scopes')]
#[ORM\UniqueConstraint(columns: ['notification_id', 'type_id'])]
#[ORM\Entity(repositoryClass: 'App\Repository\Notification\NotificationScopesRepository')]
class NotificationScopes extends BaseEntity
{
    public const DEFAULT_DISPLAY_VALUES = [
        'id',
        'subtype',
        'typeId',
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

        if (in_array('typeId', $include, true)) {
            $data['typeId'] = $this->type->getId();
        }

        if (in_array('type', $include, true)) {
            $data['type'] = $this->type->toArray(['subtype', 'name']);
        }

        if (in_array('subtype', $include, true)) {
            $data['subtype'] = $this->type->getSubType();
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
     * @var array
     */
    #[ORM\Column(name: 'value', type: 'json', nullable: true)]
    private $value;

    /**
     * @var ScopeType
     */
    #[ORM\ManyToOne(targetEntity: 'ScopeType', inversedBy: 'notificationScopes', fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'type_id', referencedColumnName: 'id', nullable: false)]
    private $type;

    /**
     * @var Notification
     */
    #[ORM\ManyToOne(targetEntity: 'Notification', inversedBy: 'scopes')]
    #[ORM\JoinColumn(name: 'notification_id', referencedColumnName: 'id', nullable: false)]
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
     * Set value.
     *
     * @param array $value
     *
     * @return NotificationScopes
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
     * @return ScopeType
     */
    public function getType(): ScopeType
    {
        return $this->type;
    }

    /**
     * @param ScopeType $type
     * @return $this
     */
    public function setType(ScopeType $type)
    {
        $this->type = $type;

        return $this;
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
