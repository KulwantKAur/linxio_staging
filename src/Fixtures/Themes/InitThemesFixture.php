<?php

namespace App\Fixtures\Themes;

use App\Entity\Theme;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

class InitThemesFixture extends BaseFixture implements FixtureGroupInterface
{
    public const THEMES = [
        [
            'name' => 'Light Theme',
            'alias' => 'light_theme',
            'theme' => [
                'accentColor' => '#512da8',
                'primaryBackgroundColor' => '#ffffff',
                'secondaryBackgroundColor' => '#f4f8f9',
                'menuBackgroundColor' => '#110A21',
                'primaryBorderColor' => '#ced4db',
                'primaryFontColor' => 'black',
                'secondaryFontColor' => '#7a7a7a',
                'logoSrc' => '/assets/images/logo-light.svg'
            ],
        ],
        [
            'name' => 'Dark Theme',
            'alias' => 'dark_theme',
            'theme' => [
                'accentColor' => '#8557d8',
                'primaryBackgroundColor' => '#2e2f30',
                'secondaryBackgroundColor' => '#212324',
                'menuBackgroundColor' => '#110A21',
                'primaryBorderColor' => 'rgba(255,255,255, 0.20)',
                'primaryFontColor' => 'white',
                'secondaryFontColor' => '#7a7a7a',
                'logoSrc' => '/assets/images/logo-dark.svg'
            ],
        ],
    ];

    public static function getGroups(): array
    {
        return [FixturesTypes::GLOBAL];
    }

    /**
     * @param ObjectManager $manager
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function load(ObjectManager $manager): void
    {
        /** @var EntityManager $manager */
        $manager = $this->prepareEntityManager($manager);

        foreach (self::THEMES as $themesData) {
            if (!$manager->getRepository(Theme::class)->findOneBy([
                'alias' => $themesData['alias']
            ])) {
                $theme = new Theme($themesData);
                $manager->persist($theme);
            }

        }

        $manager->flush();
    }
}
