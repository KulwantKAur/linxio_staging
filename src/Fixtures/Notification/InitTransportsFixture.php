<?php

namespace App\Fixtures\Notification;

use App\Entity\Notification\Transport;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

class InitTransportsFixture extends BaseFixture implements FixtureGroupInterface
{
    public const DATA = [
        ['name' => Transport::TRANSPORT_SMS, 'alias' => Transport::TRANSPORT_SMS],
        ['name' => Transport::TRANSPORT_EMAIL, 'alias' => Transport::TRANSPORT_EMAIL],
        ['name' => Transport::TRANSPORT_WEB_APP, 'alias' => Transport::TRANSPORT_WEB_APP],
        ['name' => Transport::TRANSPORT_MOBILE_APP, 'alias' => Transport::TRANSPORT_MOBILE_APP],
    ];

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return [FixturesTypes::GLOBAL];
    }

    public function load(ObjectManager $manager): void
    {
        /** @var EntityManager $manager */
        $manager = $this->prepareEntityManager($manager);
        foreach (self::DATA as $transportData) {
            $transport = $manager->getRepository(Transport::class)->findOneBy([
                'name' => $transportData['name'],
                'alias' => $transportData['alias']
            ]);
            if (!$transport) {
                $transport = new Transport();
                $transport
                    ->setAlias($transportData['alias'])
                    ->setName($transportData['name']);

                $manager->persist($transport);
            }
            $this->setReference($transportData['alias'], $transport);
        }

        $manager->flush();
    }
}
