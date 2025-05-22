<?php

namespace App\Fixtures\Notification;

use App\Entity\Notification\TemplateSet;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use App\Fixtures\Teams\InitTeamsFixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

class InitTemplateSetFixture extends BaseFixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const DATA = [
        ['name' => 'default', 'team' => null, 'path' => ''],
        ['name' => 'chevron', 'team' => null, 'path' => 'chevron/'],
    ];

    public function getDependencies(): array
    {
        return [
            InitTeamsFixture::class,
        ];
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return [FixturesTypes::GLOBAL];
    }

    public function load(ObjectManager $manager): void
    {
        /** @var EntityManager $manager */
        $manager = $this->prepareEntityManager($manager);
        foreach (self::DATA as $templateSetData) {
            $templateSet = $manager->getRepository(TemplateSet::class)->findOneBy([
                'name' => $templateSetData['name'],
                'team' => $templateSetData['team']
            ]);
            if (!$templateSet) {
                $templateSet = new TemplateSet();
                $templateSet->setName($templateSetData['name']);
                $templateSet->setPath($templateSetData['path']);

                $manager->persist($templateSet);
            }

            $this->setReference($templateSetData['name'], $templateSet);
        }
        $manager->flush();
    }
}
