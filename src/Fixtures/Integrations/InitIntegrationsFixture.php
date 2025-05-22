<?php

namespace App\Fixtures\Integrations;

use App\Entity\Integration;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class InitIntegrationsFixture extends BaseFixture implements FixtureGroupInterface
{
    public const INTEGRATIONS = [
        Integration::SOLBOX,
        Integration::PRISM,
        Integration::FLEETIO,
        Integration::VWORK,
        Integration::FUSE,
        Integration::STREAMAX,
        Integration::LOGMASTER,
        Integration::GEARBOX,
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
        foreach (self::INTEGRATIONS as $integrationName) {
            $integration = $manager->getRepository(Integration::class)->findOneBy(['name' => $integrationName]);
            if (!$integration) {
                $integration = new Integration();
                $integration->setName($integrationName);
                $manager->persist($integration);
            }
        }
        $manager->flush();
    }
}
