<?php

namespace App\Fixtures\DeviceModels;

use App\Entity\Device;
use App\Entity\DeviceModel;
use App\Entity\DeviceVendor;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class InitDeviceModelsFixture extends BaseFixture implements FixtureGroupInterface
{
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

        foreach (DeviceModel::MODELS as $vendor => $models) {
            $deviceVendor = $manager->getRepository(DeviceVendor::class)->findOneBy([
                'name' => $vendor
            ]);
            if (!$deviceVendor) {
                $deviceVendor = new DeviceVendor(['name' => $vendor, 'alias' => DeviceVendor::VENDOR_ALIAS[$vendor]]);
                $manager->persist($deviceVendor);
            } else {
                $deviceVendor->setName($vendor);
                $deviceVendor->setAlias(DeviceVendor::VENDOR_ALIAS[$vendor]);
            }
            $this->setReference($vendor, $deviceVendor);

            foreach ($models as $model) {
                /** @var DeviceModel $deviceModel */
                $deviceModel = $manager->getRepository(DeviceModel::class)->findOneBy([
                    'name' => $model['name']
                ]);
                if (!$deviceModel) {
                    $deviceModel = new DeviceModel(array_merge($model, ['vendor' => $this->getReference($vendor)]));
                    $manager->persist($deviceModel);
                } else {
                    $deviceModel->setName($model['name']);
                    $deviceModel->setUsage($model['usage'] ?? Device::USAGE_VEHICLE);
                    $deviceModel->setAlias(DeviceModel::MODEL_ALIAS[$model['name']]);
                }
                $this->setReference($model['name'], $deviceModel);
            }
        }

        $manager->flush();
    }
}