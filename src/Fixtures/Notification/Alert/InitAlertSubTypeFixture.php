<?php

namespace App\Fixtures\Notification\Alert;

use App\Entity\Notification\Alert\AlertSubType;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

class InitAlertSubTypeFixture extends BaseFixture implements FixtureGroupInterface
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

        $sort = 0;
        foreach (AlertSubType::ALERTS_SUB_TYPE as $key => $data) {
            $alertSubType = $manager->getRepository(AlertSubType::class)->findOneBy([
                'name' => $data
            ]);

            if (!$alertSubType) {
                $alertSubType = (new AlertSubType())
                    ->setName($data)
                    ->setSort($sort);

                $manager->persist($alertSubType);
            } else {
                $alertSubType->setSort($sort);
            }

            if ($alertSubType ?? null) {
                $this->setReference('alerts_subtype_' . $data, $alertSubType);
            }

            ++$sort;
        }
        $manager->flush();
    }
}
