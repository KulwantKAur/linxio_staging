<?php

namespace App\Fixtures\Plans;


use App\Entity\Plan;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class InitPlansFixture extends BaseFixture implements FixtureGroupInterface
{
    public const PLANS = [
        ['id' => 1, 'name' => Plan::PLAN_STARTER, 'displayName' => 'Starter'],
        ['id' => 2, 'name' => Plan::PLAN_ESSENTIALS, 'displayName' => 'Fleet Essentials'],
        ['id' => 3, 'name' => Plan::PLAN_PLUS, 'displayName' => 'Fleet Plus']
    ];

    public static function getGroups(): array
    {
        return [FixturesTypes::GLOBAL];
    }

    public function load(ObjectManager $manager): void
    {
        $manager = $this->prepareEntityManager($manager);
        foreach (self::PLANS as $planData) {
            $plan = $manager->getRepository(Plan::class)->findOneBy(['name' => $planData['name']]);
            if (!$plan) {
                $plan = new Plan($planData);
                $plan->setId($planData['id']);
                $manager->persist($plan);
            }
            $this->setReference($planData['name'], $plan);
        }
        $manager->flush();
    }
}