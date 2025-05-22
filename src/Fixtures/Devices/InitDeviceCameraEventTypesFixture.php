<?php

namespace App\Fixtures\Devices;

use App\Entity\DeviceCameraEventType;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class InitDeviceCameraEventTypesFixture extends BaseFixture implements FixtureGroupInterface
{
    public const DEVICE_CAMERA_EVENT_TYPES = [
        [
            'name' => DeviceCameraEventType::UNFASTENED_SEAT_BELT,
            'label' => 'Unfastened seat belt',
        ],
        [
            'name' => DeviceCameraEventType::HARSH_CORNERING,
            'label' => 'Harsh cornering',
        ],
        [
            'name' => DeviceCameraEventType::HARSH_BRAKING,
            'label' => 'Harsh braking',
        ],
        [
            'name' => DeviceCameraEventType::HARSH_ACCELERATION,
            'label' => 'Harsh acceleration',
        ],
        [
            'name' => DeviceCameraEventType::OVERSPEEDING,
            'label' => 'Overspeeding',
        ],
    ];

    public static function getGroups(): array
    {
        return [FixturesTypes::GLOBAL];
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $em = $this->prepareEntityManager($manager);

        foreach (self::DEVICE_CAMERA_EVENT_TYPES as $typeData) {
            $typeEntity = $em->getRepository(DeviceCameraEventType::class)->findOneBy([
                'name' => $typeData['name'],
            ]);

            if (!$typeEntity) {
                $typeEntity = new DeviceCameraEventType($typeData);
                $em->persist($typeEntity);
            }
        }

        $em->flush();
    }
}
