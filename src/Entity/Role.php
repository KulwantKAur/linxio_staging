<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Role
 */
#[ORM\Table(name: 'role')]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'my_entity_region')]
#[ORM\Entity(repositoryClass: 'App\Repository\RoleRepository')]
class Role extends BaseEntity
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_CLIENT_ADMIN = 'admin';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_SALES_REP = 'sales_rep';
    public const ROLE_ACCOUNT_MANAGER = 'account_manager';
    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_CLIENT_DRIVER = 'driver';
    public const ROLE_INSTALLER = 'installer';
    public const ROLE_CLIENT_INSTALLER = 'installer';
    public const ROLE_SUPPORT = 'support';
    public const ROLE_RESELLER_ADMIN = 'reseller_admin';
    public const ROLE_RESELLER_SALES_REP = 'reseller_sales_manager';
    public const ROLE_RESELLER_ACCOUNT_MANAGER = 'reseller_account_manager';
    public const ROLE_RESELLER_SUPPORT = 'reseller_support';
    public const ROLE_RESELLER_INSTALLER = 'reseller_installer';

    public const ALLOWED_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_CLIENT_ADMIN,
        self::ROLE_MANAGER,
        self::ROLE_SALES_REP,
        self::ROLE_ACCOUNT_MANAGER,
        self::ROLE_SUPER_ADMIN,
        self::ROLE_CLIENT_DRIVER,
        self::ROLE_INSTALLER,
        self::ROLE_SUPPORT,
        self::ROLE_RESELLER_ADMIN,
        self::ROLE_RESELLER_SALES_REP,
        self::ROLE_RESELLER_SUPPORT,
        self::ROLE_RESELLER_INSTALLER
    ];

    public const ADMIN_CONTROL_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_SUPER_ADMIN
    ];

    public const DEFAULT_DISPLAY_VALUES = [
        'id',
        'name',
        'team',
        'displayName'
    ];

    public const SIMPLE_DISPLAY_VALUES = [
        'id',
        'name',
        'displayName'
    ];

    /**
     * Role constructor.
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->name = $fields['name'];
        $this->team = $fields['team'];
        $this->displayName = $fields['displayName'];
    }

    /**
     * @param array $include
     * @return array
     */
    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('name', $include, true)) {
            $data['name'] = $this->name;
        }

        if (in_array('team', $include, true)) {
            $data['team'] = $this->team;
        }

        if (in_array('displayName', $include, true)) {
            $data['displayName'] = $this->displayName;
        }

        return $data;
    }

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    private $name;

    /**
     * @var string
     */
    #[ORM\Column(name: 'team', type: 'string', length: 50)]
    private $team;

    /**
     * @var string
     */
    #[ORM\Column(name: 'display_name', type: 'string', length: 255)]
    private $displayName;

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
     * Set id
     *
     * @param int $id
     *
     * @return Role
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Role
     */
    public function setRole($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set team
     *
     * @param string $team
     *
     * @return Role
     */
    public function setTeam($team)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * Get team
     *
     * @return string
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Set displayName
     *
     * @param string $displayName
     *
     * @return Role
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * Get displayName
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }
}