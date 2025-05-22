<?php

declare(strict_types = 1);

namespace App\Fixtures\DigitalForm;

use App\Entity\Depot;
use App\Entity\DigitalForm;
use App\Entity\DigitalFormSchedule;
use App\Entity\DigitalFormScheduleRecipient;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Entity\VehicleGroup;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use App\Fixtures\Vehicles\InitVehiclesFixture;
use App\Fixtures\VechilesGroup\InitVehiclesGroupFixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class InitDigitalFormScheduleFixture extends BaseFixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function getDependencies(): array
    {
        return array(
            InitDigitalFormFixture::class,
            InitVehiclesFixture::class,
            InitVehiclesGroupFixture::class,
        );
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return [FixturesTypes::TESTING];
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $manager = $this->prepareEntityManager($manager);
        $admin = $manager->getRepository(User::class)->findOneBy([
            'name' => 'Acme',
        ]);
        $vehicle = $manager->getRepository(Vehicle::class)->findOneBy([
            'team' => $admin->getTeam(),
        ]);

        $forms = [
            $this->getReference(InitDigitalFormFixture::FORM_REFERENCE_0),
            $this->getReference(InitDigitalFormFixture::FORM_REFERENCE_1),
            $this->getReference(InitDigitalFormFixture::FORM_REFERENCE_2),
        ];

        foreach ($forms as $form) {
            $schedule = new DigitalFormSchedule();
            $schedule->setDigitalForm($form);
            $schedule->setCreatedBy($admin);
            $schedule->setIsDefault(true);
            $schedule->setTimeFrom(new \Datetime('08:27'));
            $schedule->setTimeTo(new \Datetime('18:33'));
            $schedule->setDays([DigitalFormSchedule::DAY_MONDAY, DigitalFormSchedule::DAY_SUNDAY]);
            $manager->persist($schedule);

            $item = new DigitalFormScheduleRecipient();
            $item->setDigitalFormSchedule($schedule);
            $item->setType(DigitalFormScheduleRecipient::TYPE_VEHICLE);
            $item->setValue([$vehicle->getId()]);
            $manager->persist($item);

            $depot = $manager->getRepository(Depot::class)->findOneBy([
                'status' => Depot::STATUS_ACTIVE,
            ]);
            $item = new DigitalFormScheduleRecipient();
            $item->setDigitalFormSchedule($schedule);
            $item->setType(DigitalFormScheduleRecipient::TYPE_DEPOT);
            $item->setValue([$depot->getId()]);
            $manager->persist($item);

            $group = $manager->getRepository(VehicleGroup::class)->findOneBy([
                'status' => VehicleGroup::STATUS_ACTIVE,
            ]);
            $item = new DigitalFormScheduleRecipient();
            $item->setDigitalFormSchedule($schedule);
            $item->setType(DigitalFormScheduleRecipient::TYPE_GROUP);
            $item->setValue([$group->getId()]);
            $manager->persist($item);

            $manager->flush();
        }
    }
}
