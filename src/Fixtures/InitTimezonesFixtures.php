<?php

namespace App\Fixtures;

use App\Entity\TimeZone;
use App\Util\CarbonTimeZone;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class InitTimezonesFixtures extends BaseFixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return [FixturesTypes::GLOBAL];
    }

    public function load(ObjectManager $manager): void
    {
        $manager = $this->prepareEntityManager($manager);
        $timezoneIdentifiers = CarbonTimeZone::listIdentifiers();
        foreach ($timezoneIdentifiers as $timezone) {
            if ($timezone === 'UTC') {
                continue;
            }
            $tz = $manager->getRepository(TimeZone::class)->findOneBy([
                'name' => $timezone
            ]);
            if (!$tz) {
                $tz = new TimeZone();
                $tz->setName($timezone);

                $manager->persist($tz);
            }

            $this->setReference($timezone, $tz);
        }

        $manager->flush();
    }
}