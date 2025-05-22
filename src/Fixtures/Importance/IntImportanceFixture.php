<?php

namespace App\Fixtures\Importance;

use App\Entity\Importance;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class IntImportanceFixture extends BaseFixture implements FixtureGroupInterface
{

    public const IMPORTANCE = [
        ['name' => Importance::TYPE_LOW],
        ['name' => Importance::TYPE_NORMAL],
        ['name' => Importance::TYPE_AVERAGE],
        ['name' => Importance::TYPE_IMPORTANT],
        ['name' => Importance::TYPE_CRITICAL],

    ];

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return [FixturesTypes::GLOBAL];
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $manager = $this->prepareEntityManager($manager);
        foreach (self::IMPORTANCE as $importanceData) {
            $importance = $manager->getRepository(Importance::class)->findOneBy([
                'name' => $importanceData['name']
            ]);
            if (!$importance) {
                $importance = new Importance();
                $importance
                    ->setName($importanceData['name']);
                $manager->persist($importance);
            }
            $this->setReference($importanceData['name'], $importance);
        }
        $manager->flush();
    }
}