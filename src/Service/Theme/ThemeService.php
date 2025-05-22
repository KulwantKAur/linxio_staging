<?php

namespace App\Service\Theme;

use App\Entity\Theme;
use App\Entity\User;
use Doctrine\ORM\EntityManager;

/**
 * Class ThemeService
 * @package App\Service\Theme
 */
class ThemeService
{
    private $em;

    /**
     * ThemeService constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param User $user
     * @param array $entityFields
     * @return array
     */
    public function getThemes(User $user, array $entityFields = []): array
    {
        return array_map(
            static function (Theme $t) use ($entityFields) {
                return $t->toArray($entityFields);
            },
            $this->em->getRepository(Theme::class)->findThemesByTeam($user->getTeam())
        );
    }
}