<?php

namespace App\Fixtures\FuelType;

use App\Entity\FuelType\FuelIgnoreList;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

use Doctrine\Persistence\ObjectManager;

class IntFuelIgnoreListFixtures extends BaseFixture implements FixtureGroupInterface
{
    public const FUEL_TYPE_IGNORED = [
        ['name' => 'MONTHLY ADMIN CHARGE'],
        ['name' => 'Merchants Surcharge'],
        ['name' => 'Periodic Fee/Stamp Duty'],
        ['name' => 'Parts/accessories'],
        ['name' => 'SUNDRIES'],
        ['name' => 'SUNDRY - NO GST'],
        ['name' => 'SURCHARGE'],
        ['name' => 'REPAIRS'],
        ['name' => 'MANAGEMENT FEE'],
        ['name' => 'TRANSACTION FEE'],
        ['name' => 'CIGARETTES'],
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
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $manager = $this->prepareEntityManager($manager);

        foreach (self::FUEL_TYPE_IGNORED as $fuelIgnoreData) {
            $fuelIgnore = $manager->getRepository(FuelIgnoreList::class)->findOneBy([
                'name' => $fuelIgnoreData['name']
            ]);
            if (!$fuelIgnore) {
                $fuelIgnore = new FuelIgnoreList($fuelIgnoreData);
                $manager->persist($fuelIgnore);
            }

            if ($fuelIgnore ?? null) {
                $this->setReference('fuel_ignore_' . $fuelIgnoreData['name'], $fuelIgnore);
            }
        }
        $manager->flush();
    }
}
