<?php

namespace App\Fixtures\Notification\Alert;

use App\Entity\Notification\Alert\AlertType;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

class InitAlertTypeFixture extends BaseFixture implements FixtureGroupInterface
{

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return [FixturesTypes::GLOBAL];
    }

    /**
     * @param ObjectManager $manager
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function load(ObjectManager $manager): void
    {
        /** @var EntityManager $manager */
        $manager = $this->prepareEntityManager($manager);
        foreach (AlertType::ALERTS_TYPE as $key => $data) {
            $alertType = $manager->getRepository(AlertType::class)->findOneBy([
                'name' => $data
            ]);
            if (!$alertType) {
                $alertType = (new AlertType())
                    ->setName($data)
                    ->setSort(10);

                $manager->persist($alertType);
            }

            if ($alertType ?? null) {
                $this->setReference('alerts_type_' . $data, $alertType);
            }
        }
        $manager->flush();
    }
}
