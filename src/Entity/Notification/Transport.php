<?php

namespace App\Entity\Notification;

use App\Entity\BaseEntity;
use App\Entity\Setting;
use Doctrine\ORM\Mapping as ORM;

/**
 * Transport
 */
#[ORM\Table(name: 'notification_transport')]
#[ORM\Entity(repositoryClass: 'App\Repository\Notification\TransportRepository')]
class Transport extends BaseEntity
{
    public const TRANSPORT_SMS = 'sms';
    public const TRANSPORT_EMAIL = 'email';
    public const TRANSPORT_WEB_APP = 'web_app';
    public const TRANSPORT_MOBILE_APP = 'mobile_app';

    public const ALLOWED_TRANSPORTS = [
        self::TRANSPORT_SMS,
        self::TRANSPORT_EMAIL,
        self::TRANSPORT_WEB_APP,
        self::TRANSPORT_MOBILE_APP,
    ];

    public const TRANSPORT_TYPE_TO_SETTING = [
        Transport::TRANSPORT_SMS => Setting::SMS_SETTING,
        Transport::TRANSPORT_EMAIL => Setting::EMAIL_SETTING,
        Transport::TRANSPORT_WEB_APP => Setting::IN_APP_SETTING,
        Transport::TRANSPORT_MOBILE_APP => Setting::IN_APP_SETTING,
    ];

    public const TRANSPORT_TYPE_TO_RECIPIENT_TYPE = [
        NotificationRecipients::TYPE_OTHER_EMAIL=> Transport::TRANSPORT_EMAIL,
        NotificationRecipients::TYPE_OTHER_PHONE=> Transport::TRANSPORT_SMS,
    ];


    public static function getSettingsMap(): array
    {
        $map = [];
        foreach (self::TRANSPORT_TYPE_TO_SETTING as $transportAlias => $settingAlias) {
            $map[$settingAlias][] = $transportAlias;
        }

        return $map;
    }

    public const DEFAULT_DISPLAY_VALUES = [
        'id',
        'name',
        'alias',
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

        if (in_array('name', $include, true)) {
            $data['name'] = $this->name;
        }

        if (in_array('alias', $include, true)) {
            $data['alias'] = $this->alias;
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
    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    private $name;

    /**
     * @var string
     */
    #[ORM\Column(name: 'alias', type: 'string', length: 255, unique: true)]
    private $alias;

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
     * Set name.
     *
     * @param string $name
     *
     * @return Transport
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set alias.
     *
     * @param string $alias
     *
     * @return Transport
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Get alias.
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }
}
