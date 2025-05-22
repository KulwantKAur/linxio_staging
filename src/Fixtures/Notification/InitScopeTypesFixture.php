<?php

namespace App\Fixtures\Notification;

use App\Entity\Notification\ScopeType;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

class InitScopeTypesFixture extends BaseFixture implements FixtureGroupInterface
{
    public const DATA = [
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::USER,
            'subtype' => ScopeType::SUBTYPE_ANY,
            'name' => ScopeType::SUBTYPE_ANY
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::USER,
            'subtype' => ScopeType::SUBTYPE_USER,
            'name' => ScopeType::SUBTYPE_USER
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::USER,
            'subtype' => ScopeType::SUBTYPE_ROLE,
            'name' => ScopeType::SUBTYPE_ROLE
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::USER,
            'subtype' => ScopeType::SUBTYPE_TEAM,
            'name' => ScopeType::SUBTYPE_TEAM
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::USER,
            'subtype' => ScopeType::SUBTYPE_USER_GROUPS,
            'name' => ScopeType::SUBTYPE_USER_GROUPS_NAME
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::VEHICLE,
            'subtype' => ScopeType::SUBTYPE_ANY,
            'name' => ScopeType::SUBTYPE_ANY
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::VEHICLE,
            'subtype' => ScopeType::SUBTYPE_VEHICLE,
            'name' => ScopeType::SUBTYPE_VEHICLE
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::VEHICLE,
            'subtype' => ScopeType::SUBTYPE_DEPOT,
            'name' => ScopeType::SUBTYPE_DEPOT
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::VEHICLE,
            'subtype' => ScopeType::SUBTYPE_GROUP,
            'name' => ScopeType::SUBTYPE_GROUP
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::VEHICLE,
            'subtype' => ScopeType::SUBTYPE_TEAM,
            'name' => ScopeType::SUBTYPE_TEAM
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::VEHICLE,
            'subtype' => ScopeType::SUBTYPE_SENSOR,
            'name' => ScopeType::SUBTYPE_SENSOR
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::TEAM,
            'subtype' => ScopeType::SUBTYPE_ANY,
            'name' => ScopeType::SUBTYPE_ANY
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::TEAM,
            'subtype' => ScopeType::SUBTYPE_TEAM,
            'name' => ScopeType::SUBTYPE_TEAM
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::TEAM,
            'subtype' => ScopeType::SUBTYPE_TEAM_TYPE,
            'name' => ScopeType::SUBTYPE_TEAM_TYPE_NAME
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::DEVICE,
            'subtype' => ScopeType::SUBTYPE_ANY,
            'name' => ScopeType::SUBTYPE_ANY
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::DEVICE,
            'subtype' => ScopeType::SUBTYPE_DEVICE,
            'name' => ScopeType::SUBTYPE_DEVICE
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::TRACKER_HISTORY,
            'subtype' => ScopeType::SUBTYPE_ANY,
            'name' => ScopeType::SUBTYPE_ANY
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::TRACKER_HISTORY,
            'subtype' => ScopeType::SUBTYPE_DEVICE,
            'name' => ScopeType::SUBTYPE_DEVICE
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::TRACKER_HISTORY,
            'subtype' => ScopeType::SUBTYPE_VEHICLE,
            'name' => ScopeType::SUBTYPE_VEHICLE
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::TRACKER_HISTORY,
            'subtype' => ScopeType::SUBTYPE_GROUP,
            'name' => ScopeType::SUBTYPE_GROUP
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::TRACKER_HISTORY,
            'subtype' => ScopeType::SUBTYPE_DEPOT,
            'name' => ScopeType::SUBTYPE_DEPOT
        ],
        [
            'category' => ScopeType::ADDITIONAL_SCOPE_CATEGORY,
            'type' => ScopeType::AREA,
            'subtype' => ScopeType::SUBTYPE_ANY,
            'name' => ScopeType::SUBTYPE_ANY
        ],
        [
            'category' => ScopeType::ADDITIONAL_SCOPE_CATEGORY,
            'type' => ScopeType::AREA,
            'subtype' => ScopeType::SUBTYPE_AREA,
            'name' => ScopeType::SUBTYPE_AREA
        ],
        [
            'category' => ScopeType::ADDITIONAL_SCOPE_CATEGORY,
            'type' => ScopeType::AREA,
            'subtype' => ScopeType::SUBTYPE_AREAS_GROUP,
            'name' => ScopeType::SUBTYPE_AREAS_GROUP_NAME
        ],
        [
            'category' => ScopeType::ADDITIONAL_SCOPE_CATEGORY,
            'type' => ScopeType::AREA_HISTORY,
            'subtype' => ScopeType::SUBTYPE_ANY,
            'name' => ScopeType::SUBTYPE_ANY
        ],
        [
            'category' => ScopeType::ADDITIONAL_SCOPE_CATEGORY,
            'type' => ScopeType::AREA_HISTORY,
            'subtype' => ScopeType::SUBTYPE_AREA,
            'name' => ScopeType::SUBTYPE_AREA
        ],
        [
            'category' => ScopeType::ADDITIONAL_SCOPE_CATEGORY,
            'type' => ScopeType::AREA_HISTORY,
            'subtype' => ScopeType::SUBTYPE_AREAS_GROUP,
            'name' => ScopeType::SUBTYPE_AREAS_GROUP_NAME
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::AREA_HISTORY,
            'subtype' => ScopeType::SUBTYPE_ANY,
            'name' => ScopeType::SUBTYPE_ANY
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::AREA_HISTORY,
            'subtype' => ScopeType::SUBTYPE_VEHICLE,
            'name' => ScopeType::SUBTYPE_VEHICLE
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::AREA_HISTORY,
            'subtype' => ScopeType::SUBTYPE_DEPOT,
            'name' => ScopeType::SUBTYPE_DEPOT
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::AREA_HISTORY,
            'subtype' => ScopeType::SUBTYPE_GROUP,
            'name' => ScopeType::SUBTYPE_GROUP
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::DOCUMENT,
            'subtype' => ScopeType::SUBTYPE_ANY,
            'name' => ScopeType::SUBTYPE_ANY
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::DOCUMENT,
            'subtype' => ScopeType::SUBTYPE_VEHICLE,
            'name' => ScopeType::SUBTYPE_VEHICLE
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::DOCUMENT,
            'subtype' => ScopeType::SUBTYPE_DEPOT,
            'name' => ScopeType::SUBTYPE_DEPOT
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::DOCUMENT,
            'subtype' => ScopeType::SUBTYPE_GROUP,
            'name' => ScopeType::SUBTYPE_GROUP
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::DOCUMENT,
            'subtype' => ScopeType::SUBTYPE_DRIVER,
            'name' => ScopeType::SUBTYPE_DRIVER
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::DOCUMENT_RECORD,
            'subtype' => ScopeType::SUBTYPE_ANY,
            'name' => ScopeType::SUBTYPE_ANY
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::DOCUMENT_RECORD,
            'subtype' => ScopeType::SUBTYPE_VEHICLE,
            'name' => ScopeType::SUBTYPE_VEHICLE
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::DOCUMENT_RECORD,
            'subtype' => ScopeType::SUBTYPE_DEPOT,
            'name' => ScopeType::SUBTYPE_DEPOT
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::DOCUMENT_RECORD,
            'subtype' => ScopeType::SUBTYPE_GROUP,
            'name' => ScopeType::SUBTYPE_GROUP
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::DOCUMENT_RECORD,
            'subtype' => ScopeType::SUBTYPE_DRIVER,
            'name' => ScopeType::SUBTYPE_DRIVER
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::REMINDER,
            'subtype' => ScopeType::SUBTYPE_ANY,
            'name' => ScopeType::SUBTYPE_ANY
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::REMINDER,
            'subtype' => ScopeType::SUBTYPE_VEHICLE,
            'name' => ScopeType::SUBTYPE_VEHICLE
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::REMINDER,
            'subtype' => ScopeType::SUBTYPE_DEPOT,
            'name' => ScopeType::SUBTYPE_DEPOT
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::REMINDER,
            'subtype' => ScopeType::SUBTYPE_GROUP,
            'name' => ScopeType::SUBTYPE_GROUP
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::REMINDER,
            'subtype' => ScopeType::SUBTYPE_DRIVER,
            'name' => ScopeType::SUBTYPE_DRIVER
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::SERVICE_RECORD,
            'subtype' => ScopeType::SUBTYPE_ANY,
            'name' => ScopeType::SUBTYPE_ANY
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::SERVICE_RECORD,
            'subtype' => ScopeType::SUBTYPE_VEHICLE,
            'name' => ScopeType::SUBTYPE_VEHICLE
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::SERVICE_RECORD,
            'subtype' => ScopeType::SUBTYPE_DEPOT,
            'name' => ScopeType::SUBTYPE_DEPOT
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::SERVICE_RECORD,
            'subtype' => ScopeType::SUBTYPE_GROUP,
            'name' => ScopeType::SUBTYPE_GROUP
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::SERVICE_RECORD,
            'subtype' => ScopeType::SUBTYPE_DRIVER,
            'name' => ScopeType::SUBTYPE_DRIVER
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::ASSET,
            'subtype' => ScopeType::SUBTYPE_ANY,
            'name' => ScopeType::SUBTYPE_ANY
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::ASSET,
            'subtype' => ScopeType::SUBTYPE_ASSET,
            'name' => ScopeType::SUBTYPE_ASSET
        ],
        [
            'category' => ScopeType::GENERAL_SCOPE_CATEGORY,
            'type' => ScopeType::ANY,
            'subtype' => ScopeType::ANY,
            'name' => ScopeType::ANY
        ]
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
        foreach (self::DATA as $typeData) {
            /** @var ScopeType $scopeType */
            $scopeType = $manager->getRepository(ScopeType::class)->findOneBy([
                'category' => $typeData['category'],
                'type' => $typeData['type'],
                'subType' => $typeData['subtype']
            ]);

            if (!$scopeType) {
                $scopeType = new ScopeType();
                $scopeType
                    ->setType($typeData['type'])
                    ->setSubType($typeData['subtype'])
                    ->setName($typeData['name'])
                    ->setCategory($typeData['category']);

                $manager->persist($scopeType);
            } else {
                $scopeType->setCategory($typeData['category']);
                $scopeType->setName($typeData['name']);
            }

            $this->setReference(implode('_', [$typeData['type'], $typeData['subtype'], $typeData['category']]), $scopeType);
        }

        $manager->flush();
    }
}
