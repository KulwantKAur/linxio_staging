<?php

namespace App\Fixtures\SSOIntegrations;

use App\Entity\SSOIntegration;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class InitSSOIntegrationsFixture extends BaseFixture implements FixtureGroupInterface
{
    public const INTEGRATIONS = [
        ['name' => SSOIntegration::OKTA, 'label' => 'Okta'],
        ['name' => SSOIntegration::MICROSOFT_AZURE, 'label' => 'Microsoft Azure'],
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
        foreach (self::INTEGRATIONS as $integrationData) {
            $name = $integrationData['name'];
            $label = $integrationData['label'];
            $integration = $manager->getRepository(SSOIntegration::class)->findOneBy(['name' => $name]);

            if (!$integration) {
                $integration = new SSOIntegration();
                $integration->setName($name);
                $integration->setLabel($label);
                $manager->persist($integration);
            }
        }

        $manager->flush();
    }
}
