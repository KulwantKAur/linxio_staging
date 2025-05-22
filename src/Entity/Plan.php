<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Plan
 */
#[ORM\Table(name: 'plan')]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'my_entity_region')]
#[ORM\Entity(repositoryClass: 'App\Repository\PlanRepository')]
class Plan extends BaseEntity
{
    public const PLAN_STARTER = 'starter';
    public const PLAN_ESSENTIALS = 'fleet_essentials';
    public const PLAN_PLUS = 'fleet_plus';

    public const CHEVRON_PLAN_ESSENTIALS = 'Standard Plan';
    public const CHEVRON_PLAN_PLUS = 'Premium Plan';

    public const CHEVRON_ALIAS = [
        self::PLAN_ESSENTIALS => self::CHEVRON_PLAN_ESSENTIALS,
        self::PLAN_PLUS => self::CHEVRON_PLAN_PLUS
    ];

    public const PLAN_DEFAULT_SETTINGS = [
        self::PLAN_STARTER => [
            ['role' => null, 'name' => Setting::SMS_SETTING, 'value' => Setting::DISABLED],
            ['role' => null, 'name' => Setting::TRACKING_LINK, 'value' => Setting::TRACKING_LINK_VALUE],
            ['role' => null, 'name' => Setting::DIGITAL_FORM, 'value' => Setting::DIGITAL_FORM_DEFAULT_VALUE],
        ],
        self::PLAN_ESSENTIALS => [
            ['role' => null, 'name' => Setting::SMS_SETTING, 'value' => Setting::DISABLED],
            ['role' => null, 'name' => Setting::TRACKING_LINK, 'value' => Setting::TRACKING_LINK_VALUE],
            ['role' => null, 'name' => Setting::DIGITAL_FORM, 'value' => Setting::DIGITAL_FORM_DEFAULT_VALUE],
        ],
        self::PLAN_PLUS => [
            ['role' => null, 'name' => Setting::SMS_SETTING, 'value' => Setting::ENABLED],
            ['role' => null, 'name' => Setting::TRACKING_LINK, 'value' => true],
            ['role' => null, 'name' => Setting::DIGITAL_FORM, 'value' => true],
        ]
    ];

    public function __construct(array $fields)
    {
        $this->name = $fields['name'];
        $this->displayName = $fields['displayName'];
    }

    public function toArray(array $include = [], ?Team $team = null): array
    {
        $name = $team && $team->isChevron() && isset(self::CHEVRON_ALIAS[$this->name])
            ? self::CHEVRON_ALIAS[$this->name] : $this->displayName;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'displayName' => $name,
        ];
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
    #[ORM\Column(name: 'display_name', type: 'string', length: 255)]
    private $displayName;

    /**
     * @var string
     */
    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    private $name;

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
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Set displayName
     *
     * @param string displayName
     *
     * @return Plan
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

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Plan
     */
    public function setName($name)
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
}

