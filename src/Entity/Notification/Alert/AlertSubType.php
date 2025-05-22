<?php

namespace App\Entity\Notification\Alert;

use App\Entity\BaseEntity;
use App\Entity\Permission;
use App\Entity\Plan;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * AlertSubType
 *
 *
 * @UniqueEntity(
 *     fields={"name"},
 *     errorPath="name",
 *     message="Alert subtype with this name already exists."
 * )
 */
#[ORM\Table(name: 'notification_alert_subtype')]
#[ORM\Entity(repositoryClass: 'App\Repository\Notification\Alert\AlertSubTypeRepository')]
class AlertSubType extends BaseEntity
{
    public const SUBTYPE_ADMIN_USER_ACCOUNTS = 'Admin user accounts';
    public const SUBTYPE_ADMIN_ACTIVITIES = 'Admin activities';
    public const SUBTYPE_CLIENTS_ACCOUNTS = 'Client accounts';
    public const SUBTYPE_DEVICE_STATUS = 'Device status';
//    public const SUBTYPE_GPS_DATA = 'GPS Device Data';

    public const SUBTYPE_VEHICLE_ACTIVITIES = 'Vehicle Activities';
    public const SUBTYPE_VEHICLE_DOCUMENTS = 'Vehicle Documents';
    public const SUBTYPE_ASSET_DOCUMENTS = 'Asset Documents';
    public const SUBTYPE_ASSET_ACTIVITIES = 'Asset Activities';
    public const SUBTYPE_DIGITAL_FORMS = 'Digital Forms';
    public const SUBTYPE_DRIVER_ACTIVITIES = 'Driver activities';
    public const SUBTYPE_SENSOR_DATA = 'Sensor Data';
    public const SUBTYPE_DRIVER_DOCUMENTS = 'Driver Documents';
    public const SUBTYPE_VEHICLE_STATUS = 'Vehicle status';
    public const SUBTYPE_AREAS = 'Areas';
    public const SUBTYPE_SERVICE_REMINDERS = 'Service reminders';
    public const SUBTYPE_VEHICLE_DATA = 'Vehicle data';
    public const SUBTYPE_USER_ACCOUNTS = 'User accounts';
    public const SUBTYPE_STRIPE_INTEGRATION = 'Stripe integration';
    public const SUBTYPE_XERO_INTEGRATION = 'Xero integration';
    public const SUBTYPE_BILLING_INFO = 'Billing';
    public const SUBTYPE_BILLING_INFO_ADMIN = 'Billing admin';

    public const ALERTS_SUB_TYPE = [
        /* for admin */
        AlertSubType::SUBTYPE_ADMIN_USER_ACCOUNTS,
        AlertSubType::SUBTYPE_ADMIN_ACTIVITIES,
        AlertSubType::SUBTYPE_CLIENTS_ACCOUNTS,
        AlertSubType::SUBTYPE_DEVICE_STATUS,
        AlertSubType::SUBTYPE_STRIPE_INTEGRATION,
        AlertSubType::SUBTYPE_XERO_INTEGRATION,
//        AlertSubType::SUBTYPE_GPS_DATA,
        /* for client */
        AlertSubType::SUBTYPE_VEHICLE_ACTIVITIES,
        AlertSubType::SUBTYPE_VEHICLE_DOCUMENTS,
        AlertSubType::SUBTYPE_DIGITAL_FORMS,
        AlertSubType::SUBTYPE_DRIVER_ACTIVITIES,
        AlertSubType::SUBTYPE_SENSOR_DATA,
        AlertSubType::SUBTYPE_DRIVER_DOCUMENTS,
        AlertSubType::SUBTYPE_VEHICLE_STATUS,
        AlertSubType::SUBTYPE_AREAS,
        AlertSubType::SUBTYPE_SERVICE_REMINDERS,
        AlertSubType::SUBTYPE_VEHICLE_DATA,
        AlertSubType::SUBTYPE_USER_ACCOUNTS,
        AlertSubType::SUBTYPE_ASSET_DOCUMENTS,
        AlertSubType::SUBTYPE_ASSET_ACTIVITIES,
        AlertSubType::SUBTYPE_BILLING_INFO,
        AlertSubType::SUBTYPE_BILLING_INFO_ADMIN,
    ];

    public const PERMISSIONS_BY_ALERT_SUB_TYPE = [
        self::SUBTYPE_ADMIN_USER_ACCOUNTS => Permission::ADMIN_TEAM_USER_LIST,
        self::SUBTYPE_ADMIN_ACTIVITIES => Permission::ADMIN_TEAM_USER_LIST,
        self::SUBTYPE_CLIENTS_ACCOUNTS => Permission::CLIENT_LIST,
        self::SUBTYPE_DEVICE_STATUS => Permission::DEVICE_LIST,
        self::SUBTYPE_STRIPE_INTEGRATION => Permission::DEVICE_LIST,
//        self::SUBTYPE_GPS_DATA => Permission::DEVICE_LIST,
        self::SUBTYPE_VEHICLE_ACTIVITIES => Permission::FLEET_SECTION_FLEET,
        self::SUBTYPE_VEHICLE_DOCUMENTS => Permission::VEHICLE_DOCUMENT_LIST,
        self::SUBTYPE_DIGITAL_FORMS => Permission::VEHICLE_INSPECTION_FORM_LIST,
        self::SUBTYPE_DRIVER_ACTIVITIES => Permission::DRIVER_LIST,
        self::SUBTYPE_SENSOR_DATA => Permission::DEVICE_SENSOR_LIST,
        self::SUBTYPE_DRIVER_DOCUMENTS => Permission::DRIVER_DOCUMENT_LIST,
        self::SUBTYPE_VEHICLE_STATUS => Permission::FLEET_SECTION_FLEET,
        self::SUBTYPE_AREAS => Permission::AREA_LIST,
        self::SUBTYPE_SERVICE_REMINDERS => Permission::VEHICLE_REMINDER_LIST,
        self::SUBTYPE_VEHICLE_DATA => Permission::FLEET_SECTION_FLEET,
        self::SUBTYPE_USER_ACCOUNTS => Permission::CONFIGURATION_USERS,
        self::SUBTYPE_ASSET_DOCUMENTS => Permission::ASSET_DOCUMENT_LIST,
        self::SUBTYPE_ASSET_ACTIVITIES => Permission::ASSET_LIST,
        self::SUBTYPE_BILLING_INFO => Permission::BILLING_INVOICE_VIEW,
//        self::SUBTYPE_STRIPE_INTEGRATION => Permission::??,
    ];

    public const DISPLAYED_VALUES = [
        'id',
        'name',
        'events',
        'team',
    ];

    public function __construct()
    {
        $this->alertSettings = new ArrayCollection();
    }

    /**
     * @param array $include
     * @param User|null $user
     * @return array
     */
    public function toArray(array $include = [], ?User $user = null): array
    {
        $data = [];

        if (empty($include)) {
            $include = self::DISPLAYED_VALUES;
        }

        if (in_array('id', $include, true)) {
            $data['id'] = $this->getId();
        }

        if (in_array('name', $include, true)) {
            $data['name'] = $this->getName();
        }

        if (in_array('events', $include, true)) {
            $data['events'] = $this->getEventArray();
        }

        if ($user && in_array('events', $include, true)) {
            $data['events'] = $this->getEventArrayByPlan($user->getPlan());
        }

        if (in_array('sort', $include, true)) {
            $data['sort'] = $this->getSort();
        }

        if (in_array('team', $include, true)) {
            $data['team'] = $this->getTeam();
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
     *
     *
     * @Assert\Length(
     *      min = 1,
     *      max = 255
     * )
     * @Assert\NotBlank
     */
    #[ORM\Column(name: 'name', type: 'string', length: 255, unique: true)]
    private $name;

    /**
     * @var int
     */
    #[ORM\Column(name: 'sort', type: 'integer', options: ['default' => 10])]
    private $sort;

    /**
     * @var ArrayCollection|AlertSetting[]
     */
    #[ORM\JoinTable(name: 'notification_alert_setting')]
    #[ORM\JoinColumn(name: 'alert_subtype_id', referencedColumnName: 'id')]
    #[ORM\OneToMany(targetEntity: 'App\Entity\Notification\Alert\AlertSetting', mappedBy: 'alertSubtype', fetch: 'EXTRA_LAZY')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private $alertSettings;

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
     * @return AlertSubType
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
     * Set sort.
     *
     * @param int $sort
     *
     * @return AlertSubType
     */
    public function setSort($sort)
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * Get sort.
     *
     * @return int
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @return ArrayCollection|AlertSetting[]
     */
    public function getAlertSetting()
    {
        return $this->alertSettings;
    }

    /**
     * @return array
     */
    public function getEventArray(): array
    {
        return $this->getAlertSetting()->map(
            static function (AlertSetting $alertSetting) {
                return $alertSetting->getEventArray();
            }
        )->toArray();
    }

    public function getEventArrayByPlan(?Plan $plan): array
    {
        $alertSettings = $this->getAlertSetting();

        if ($plan) {
            $alertSettings = $alertSettings->filter(
                function (AlertSetting $alertSetting) use ($plan) {
                    return $alertSetting->getPlan() && $alertSetting->getPlan()->getId() == $plan->getId();
                }
            );
        } else {
            $alertSettingIdForAdmin = [];
            $alertSettings = $alertSettings->filter(
                function (AlertSetting $alertSetting) use ($plan, &$alertSettingIdForAdmin) {
                    $eventId = $alertSetting->getEvent()->getId();
                    if (!in_array($eventId, $alertSettingIdForAdmin)) {
                        $alertSettingIdForAdmin[] = $eventId;
                        return true;
                    } else {
                        return false;
                    }
                }
            );
        }

        return $alertSettings->map(
            static function (AlertSetting $alertSetting) {
                return $alertSetting->getEventArray();
            }
        )->getValues();
    }

    /**
     * @return string
     */
    public function getTeam(): string
    {
        return $this->getAlertSetting()->map(
            static function (AlertSetting $alertSetting) {
                return $alertSetting->getTeam();
            }
        )->first();
    }
}
