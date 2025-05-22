<?php

namespace App\Fixtures\StreamaxIntegrations;

use App\Entity\StreamaxIntegration;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class InitStreamaxIntegrationsFixture extends BaseFixture implements FixtureGroupInterface
{
    public const INTEGRATIONS = [
        [
            'name' => 'Matthew',
            'url' => 'https://ap-ftcloud.ifleetvision.com:20501',
            'tenantId' => 908,
            'secret' => 'secret',
            'signature' => 'signature'
        ]
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
            $integration = $manager->getRepository(StreamaxIntegration::class)
                ->findOneBy(['tenantId' => $integrationData['tenantId']]);

            if (!$integration) {
                $integration = new StreamaxIntegration($integrationData);
                $manager->persist($integration);
            }
        }

        $manager->flush();
    }
}
