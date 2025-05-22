<?php

namespace App\Entity;

use App\Util\AttributesTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Theme
 */
#[ORM\Table(name: 'theme')]
#[ORM\Entity(repositoryClass: 'App\Repository\ThemeRepository')]
class Theme extends BaseEntity
{
    use AttributesTrait;

    public const DEFAULT_THEME_ALIAS = 'light_theme';

    public const DEFAULT_DISPLAY_VALUES = [
        'id',
        'name',
        'theme',
    ];

    public function __construct(array $fields)
    {
        $this->name = $fields['name'] ?? null;
        $this->alias = $fields['alias'] ?? null;
        $this->theme = $fields['theme'] ?? null;
    }

    public function toArray($include = []): array
    {
        $data = [];

        $data['id'] = $this->getId();

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('name', $include, true)) {
            $data['name'] = $this->name;
        }

        if (in_array('theme', $include, true)) {
            $data = array_merge($data, $this->theme);
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
     * @var array|null
     */
    #[ORM\Column(name: 'theme', type: 'json', nullable: true)]
    private $theme;

    /**
     * @var Team
     */
    #[ORM\OneToOne(targetEntity: 'Team')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id', nullable: true)]
    private $team;

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     * @return $this
     */
    public function setAlias(string $alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getTheme(): ?array
    {
        return $this->theme;
    }

    /**
     * @param array|null $theme
     */
    public function setTheme(?array $theme): void
    {
        $this->theme = $theme;
    }

    /**
     * @param Team $team
     * @return $this
     */
    public function setTeam(Team $team)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * Get team
     *
     * @return Team
     */
    public function getTeam(): Team
    {
        return $this->team;
    }

    /**
     * Get teamId
     *
     * @return int
     */
    public function getTeamId()
    {
        return $this->getTeam()->getId();
    }
}
