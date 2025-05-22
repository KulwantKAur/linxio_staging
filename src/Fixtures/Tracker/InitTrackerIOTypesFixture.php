<?php

namespace App\Fixtures\Tracker;

use App\Entity\Tracker\TrackerIOType;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class InitTrackerIOTypesFixture extends BaseFixture implements FixtureGroupInterface
{
    private const IO_TYPES = [
        ['id' => 1, 'name' => TrackerIOType::AC_DIGITAL_INPUT, 'label' => 'AC Digital Input'],
        ['id' => 2, 'name' => TrackerIOType::IGNITION_INPUT, 'label' => 'Ignition Input'],
        ['id' => 3, 'name' => TrackerIOType::DIGITAL_INPUT_1, 'label' => 'Digital Input 1'],
        ['id' => 4, 'name' => TrackerIOType::DIGITAL_INPUT_2, 'label' => 'Digital Input 2'],
        ['id' => 5, 'name' => TrackerIOType::DIGITAL_INPUT_3, 'label' => 'Digital Input 3'],
        ['id' => 6, 'name' => TrackerIOType::DIGITAL_INPUT_4, 'label' => 'Digital Input 4'],
        ['id' => 7, 'name' => TrackerIOType::DIGITAL_INPUT_5, 'label' => 'Digital Input 5'],
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

        foreach (self::IO_TYPES as $IOType) {
            $trackerIOType = $manager->getRepository(TrackerIOType::class)->findOneBy(['name' => $IOType['name']]);

            if (!$trackerIOType) {
                $trackerIOType = new TrackerIOType();
                $trackerIOType->setName($IOType['name']);
                $trackerIOType->setLabel($IOType['label']);
                $manager->persist($trackerIOType);
            }
        }

        $manager->flush();
    }
}