<?php

namespace App\Fixtures\ReminderCategories;

use App\Entity\ReminderCategory;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use App\Fixtures\Permissions\InitPermissionsFixture;
use App\Fixtures\Roles\InitRolesFixture;
use App\Fixtures\Teams\InitTeamsFixture;
use App\Fixtures\Users\InitDemoUsersFixtures;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class InitReminderCategoriesFixture extends BaseFixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function getDependencies(): array
    {
        return [
            InitPermissionsFixture::class,
            InitRolesFixture::class,
            InitTeamsFixture::class
        ];
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return [FixturesTypes::GLOBAL];
    }

    const CATEGORIES = [
        [
            'name' => 'General Maintenance'
        ],
        [
            'name' => 'Oil Change'
        ],
        [
            'name' => 'Tyre Maintenance'
        ],
        [
            'name' => 'Vehicle Check'
        ],
        [
            'name' => 'Fixed mileage service',
            'fixedMileage' => true
        ]
    ];

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $manager = $this->prepareEntityManager($manager);

        foreach (self::CATEGORIES as $categoryData) {
            $category = $manager->getRepository(ReminderCategory::class)->findOneBy([
                'name' => $categoryData['name'],
            ]);
            if (!$category) {
                $category = new ReminderCategory($categoryData);
                $manager->persist($category);
            }
        }
        $manager->flush();
    }
}
