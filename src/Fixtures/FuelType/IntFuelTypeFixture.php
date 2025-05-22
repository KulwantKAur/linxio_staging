<?php

namespace App\Fixtures\FuelType;

use App\Entity\FuelType\FuelType;
use App\Entity\FuelType\FuelMapping;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

use Doctrine\Persistence\ObjectManager;

class IntFuelTypeFixture extends BaseFixture implements FixtureGroupInterface
{
    public const FUEL_TYPE = [
        ['name' => FuelType::FUEL_TYPE_DIESEL],
        ['name' => FuelType::FUEL_TYPE_PETROL],
        ['name' => FuelType::FUEL_TYPE_GAS],
        ['name' => FuelType::FUEL_TYPE_ELECTRIC],
    ];

    public const FUEL_MAPPING = [
        FuelType::FUEL_TYPE_DIESEL => [
            ['name' => 'ULS DIESEL (50)'],
            ['name' => 'V-POWER'],
            ['name' => 'DIESEL'],
        ],
        FuelType::FUEL_TYPE_PETROL => [
            ['name' => 'REGULAR ULP'],
            ['name' => 'E10 ULP'],
            ['name' => 'ULP ETHANOL'],
            ['name' => 'PULP (RON 95)'],
            ['name' => 'ULTRA PULP'],
            ['name' => 'ULP'],
            ['name' => 'VORTEX'],
            ['name' => 'UNLEADED 95'],
            ['name' => 'UNLEADED PETROL'],
            ['name' => 'UNLEADED E10'],
            ['name' => 'PREMIUM UNLEADED'],
            ['name' => 'Unleaded'],
        ],
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

        foreach (self::FUEL_TYPE as $fuelTypeData) {
            $fuelType = $manager->getRepository(FuelType::class)->findOneBy([
                'name' => $fuelTypeData['name']
            ]);
            if (!$fuelType) {
                $fuelType = new FuelType();
                $fuelType
                    ->setName($fuelTypeData['name']);
                $manager->persist($fuelType);
            }

            if (isset(self::FUEL_MAPPING[$fuelTypeData['name']])) {
                foreach (self::FUEL_MAPPING[$fuelTypeData['name']] as $fuelMappingData) {
                    $fuelMappingObj = $manager->getRepository(FuelMapping::class)->findOneBy([
                        'name' => $fuelMappingData['name']
                    ]);
                    if (!$fuelMappingObj) {
                        $fuelMappingObj = new FuelMapping();
                        $fuelMappingObj->setName($fuelMappingData['name']);
                        $fuelMappingObj->setFuelType($fuelType);
                        $manager->persist($fuelMappingObj);
                    }
                }
            }
            if ($fuelType ?? null) {
                $this->setReference('fuel_type_' . $fuelTypeData['name'], $fuelType);
            }
        }
        $manager->flush();
    }
}
