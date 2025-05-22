<?php

namespace App\Fixtures\Devices;

use App\Entity\Client;
use App\Entity\Device;
use App\Entity\DeviceInstallation;
use App\Entity\DeviceModel;
use App\Entity\DeviceVendor;
use App\Entity\Vehicle;
use App\Events\Device\DeviceInstalledEvent;
use App\Fixtures\BaseFixture;
use App\Fixtures\DeviceModels\InitDeviceModelsFixture;
use App\Fixtures\FixturesTypes;
use App\Fixtures\Permissions\InitPermissionsFixture;
use App\Fixtures\Roles\InitRolesFixture;
use App\Fixtures\Teams\InitTeamsFixture;
use App\Fixtures\Users\InitDemoUsersFixtures;
use App\Fixtures\Vehicles\InitVehiclesFixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InitDevicesFixture extends BaseFixture implements
    DependentFixtureInterface,
    FixtureGroupInterface,
    ContainerAwareInterface
{
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
        return [FixturesTypes::TESTING];
    }

    public function getDependencies(): array
    {
        return array(
            InitPermissionsFixture::class,
            InitRolesFixture::class,
            InitDemoUsersFixtures::class,
            InitTeamsFixture::class,
            InitVehiclesFixture::class,
            InitDeviceModelsFixture::class
        );
    }

    const DEVICES = [
        [
            'imei' => '867060038028151',
            'phone' => '+375445154982',
            'model' => DeviceModel::TELTONIKA_FM3001
        ],
        [
            'imei' => '866425033530223',
            'phone' => '+375445163629',
            'model' => DeviceModel::TELTONIKA_FM36M1
        ],
        [
            'imei' => '862259588834290',
            'phone' => '+375111111111',
            'model' => DeviceModel::TELTONIKA_FM3001
        ],
        [
            'imei' => '866425030756532',
            'phone' => '+61498487335',
            'sn' => '1100077292',
            'model' => DeviceModel::TELTONIKA_FM36M1
        ],
        [
            'imei' => '861107034113663',
            'phone' => '',
            'sn' => '',
            'model' => DeviceModel::ULBOTECH_T301
        ],
        [
            'imei' => '866425035404484',
            'phone' => '',
            'sn' => '',
            'model' => DeviceModel::TOPFLYTECH_TLD1_A_E
        ],
        [
            'imei' => '880616898888888',
            'phone' => '',
            'sn' => '',
            'model' => DeviceModel::TOPFLYTECH_TLD1_DA_DE
        ],
        [
            'imei' => '880616898888889',
            'phone' => '',
            'sn' => '',
            'model' => DeviceModel::TOPFLYTECH_TLP1_LF
        ],
        [
            'imei' => '880616898888890',
            'phone' => '',
            'sn' => '',
            'model' => DeviceModel::TOPFLYTECH_TLD2_DA_DE
        ],
    ];

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $manager = $this->prepareEntityManager($manager);
        $client = $manager->getRepository(Client::class)->findOneBy([
            'name' => 'ACME1'
        ]);
        $devices = [];

        foreach (self::DEVICES as $device) {
            $deviceEntity = $manager->getRepository(Device::class)->findOneBy([
                'imei' => $device['imei'],
            ]);
            if (!$deviceEntity) {
                $deviceEntity = new Device($device);
                $deviceEntity->setTeam($client->getTeam());
                $deviceModel = $this->getReference($device['model']);
                $deviceEntity->setModel($deviceModel);
                $deviceEntity->setUsage($deviceModel->getUsage());
                $manager->persist($deviceEntity);
            }
            $devices[] = $deviceEntity;
        }

        $vehicles = $manager->getRepository(Vehicle::class)->findAll();
        foreach ($vehicles as $key => $vehicle) {
            if (!isset($devices[$key])) {
                continue;
            }
            $installedDevice = $manager->getRepository(DeviceInstallation::class)
                ->findByDeviceImeiOrVehicleRegNo($devices[$key]->getImei());
            $installedVehicle = $manager->getRepository(DeviceInstallation::class)
                ->findByDeviceImeiOrVehicleRegNo(null, $vehicle->getRegNo());

            if (!$installedDevice && !$installedVehicle && isset($devices[$key])) {
                $deviceInstallation = new DeviceInstallation([
                    'vehicle' => $vehicle,
                    'device' => $devices[$key],
                    'installDate' => new \DateTime()
                ]);

                $manager->persist($deviceInstallation);
                $devices[$key]->install($deviceInstallation);

                $this->container->get('event_dispatcher')->dispatch(
                    new DeviceInstalledEvent($devices[$key]),
                    DeviceInstalledEvent::NAME
                );
            }
        }

        $manager->flush();

        $this->generateSimulatorDevices();
    }

    /**
     * @throws \Exception
     */
    public function generateSimulatorDevices()
    {
        $trackerSimulatorFactory = $this->container->get('tracker.simulator_tracker_factory');
        $simulatorTrackerService = $trackerSimulatorFactory->getInstance(DeviceVendor::VENDOR_TELTONIKA);
        $simulatorTrackerService->generateDevices();
    }
}
