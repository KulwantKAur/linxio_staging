<?php

namespace App\Fixtures\Tracker;

use App\Entity\DeviceModel;
use App\Entity\Tracker\Teltonika\TrackerSensorEvent;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use App\Service\Tracker\Parser\Teltonika\SensorEventTypes\BaseType;
use App\Service\Tracker\Parser\Teltonika\SensorEventTypes\FM3001;
use App\Service\Tracker\Parser\Teltonika\SensorEventTypes\FM36M1;
use App\Service\Tracker\Parser\Teltonika\SensorEventTypes\FMB920;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

class InitSensorEventsFixture extends BaseFixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return [FixturesTypes::GLOBAL];
    }

    /**
     * @param ObjectManager $manager
     * @throws \ReflectionException
     */
    public function load(ObjectManager $manager): void
    {
        /** @var EntityManager $manager */
        $manager = $this->prepareEntityManager($manager);

        $this->addModelEvents($manager, FM3001::class);
        $this->addModelEvents($manager, FM36M1::class);
        $this->addModelEvents($manager, FMB920::class);
    }

    /**
     * @param ObjectManager $manager
     * @param $eventTypeModel
     * @throws \ReflectionException
     */
    private function addModelEvents(ObjectManager $manager, $eventTypeModel)
    {
        $rc = new \ReflectionClass($eventTypeModel);
        $constants = $rc->getConstants();
        $deviceModel = $manager->getRepository(DeviceModel::class)->findOneBy([
            'name' => $eventTypeModel::getModelName()
        ]);

        if ($deviceModel) {
            foreach ($constants as $name => $id) {
                if (BaseType::hasNoConstant($name)) {
                    $sensorEvent = $manager->getRepository(TrackerSensorEvent::class)->findOneBy([
                        'remoteId' => $id,
                        'deviceModel' => $deviceModel
                    ]);
                    if (!$sensorEvent) {
                        $eventName = strtolower(substr($name, 0, -3));
                        $eventLabel = str_replace('_', ' ', ucfirst($eventName));
                        $event = new TrackerSensorEvent();
                        $event->setRemoteId($id);
                        $event->setName($eventName);
                        $event->setLabel($eventLabel);
                        $event->setDeviceModel($deviceModel);
                        $manager->persist($event);
                    }
                }
            }
        }

        $manager->flush();
    }
}