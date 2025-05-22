<?php

namespace App\Fixtures\Devices;

use App\Entity\DeviceSensorType;
use App\Entity\DeviceVendor;
use App\Fixtures\BaseFixture;
use App\Fixtures\DeviceModels\InitDeviceModelsFixture;
use App\Fixtures\FixturesTypes;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InitDeviceSensorTypesFixture extends BaseFixture implements
    DependentFixtureInterface,
    FixtureGroupInterface,
    ContainerAwareInterface
{
    public const DEVICE_SENSOR_TYPES = [
        [
            'vendor' => DeviceVendor::VENDOR_TOPFLYTECH,
            'name' => DeviceSensorType::TOPFLYTECH_IBUTTON_TYPE,
            'label' => 'IButton',
            'isAvailable' => true,
        ],
        [
            'vendor' => DeviceVendor::VENDOR_TOPFLYTECH,
            'name' => DeviceSensorType::TOPFLYTECH_TEMP_AND_HUMIDITY_TYPE,
            'label' => 'Temp & humidity',
            'isAvailable' => true,
        ],
        [
            'vendor' => DeviceVendor::VENDOR_TOPFLYTECH,
            'name' => DeviceSensorType::TOPFLYTECH_TRACKING_BEACON_TYPE,
            'label' => 'Tracking Beacon',
            'isAvailable' => true,
        ],
    ];

    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public static function getGroups(): array
    {
        return [FixturesTypes::GLOBAL];
    }

    public function getDependencies(): array
    {
        return [
            InitDeviceModelsFixture::class
        ];
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $em = $this->prepareEntityManager($manager);

        foreach (self::DEVICE_SENSOR_TYPES as $deviceSensorType) {
            $deviceSensorTypeEntity = $em->getRepository(DeviceSensorType::class)->findOneBy([
                'name' => $deviceSensorType['name'],
            ]);

            if (!$deviceSensorTypeEntity) {
                $deviceSensorTypeEntity = new DeviceSensorType($deviceSensorType);
                $deviceSensorTypeVendor = $this->getReference($deviceSensorType['vendor']);
                $deviceSensorTypeEntity->setVendor($deviceSensorTypeVendor);
                $em->persist($deviceSensorTypeEntity);
            }
        }

        $em->flush();
    }
}
