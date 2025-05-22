<?php

namespace App\Fixtures\Roles;

use App\Entity\Role;
use App\Entity\Team;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class InitRolesFixture extends BaseFixture implements FixtureGroupInterface
{
    public const ROLES_ADMIN_TEAM = [
        ['id' => 1, 'name' => Role::ROLE_SUPER_ADMIN, 'team' => Team::TEAM_ADMIN, 'displayName' => 'SuperAdmin'],
        ['id' => 2, 'name' => Role::ROLE_ADMIN, 'team' => Team::TEAM_ADMIN, 'displayName' => 'Admin'],
        ['id' => 3, 'name' => Role::ROLE_SALES_REP, 'team' => Team::TEAM_ADMIN, 'displayName' => 'Sales Representative'],
        ['id' => 4, 'name' => Role::ROLE_INSTALLER, 'team' => Team::TEAM_ADMIN, 'displayName' => 'Installer'],
        ['id' => 5, 'name' => Role::ROLE_SUPPORT, 'team' => Team::TEAM_ADMIN, 'displayName' => 'Support'],
        ['id' => 14, 'name' => Role::ROLE_ACCOUNT_MANAGER, 'team' => Team::TEAM_ADMIN, 'displayName' => 'Account Manager']
    ];

    public const ROLES_CLIENT_TEAM = [
        ['id' => 6, 'name' => Role::ROLE_ADMIN, 'team' => Team::TEAM_CLIENT, 'displayName' => 'Admin'],
        ['id' => 7, 'name' => Role::ROLE_MANAGER, 'team' => Team::TEAM_CLIENT, 'displayName' => 'Manager'],
        ['id' => 8, 'name' => Role::ROLE_CLIENT_DRIVER, 'team' => Team::TEAM_CLIENT, 'displayName' => 'Driver'],
    ];

    public const ROLES_RESELLER_TEAM = [
        ['id' => 9, 'name' => Role::ROLE_RESELLER_ADMIN, 'team' => Team::TEAM_RESELLER, 'displayName' => 'Reseller Admin'],
        ['id' => 10, 'name' => Role::ROLE_RESELLER_SALES_REP, 'team' => Team::TEAM_RESELLER, 'displayName' => 'Reseller Sales Representative'],
        ['id' => 15, 'name' => Role::ROLE_RESELLER_ACCOUNT_MANAGER, 'team' => Team::TEAM_RESELLER, 'displayName' => 'Reseller Account Manager'],
        ['id' => 11, 'name' => Role::ROLE_RESELLER_SUPPORT, 'team' => Team::TEAM_RESELLER, 'displayName' => 'Reseller Support'],
        ['id' => 13, 'name' => Role::ROLE_RESELLER_INSTALLER, 'team' => Team::TEAM_RESELLER, 'displayName' => 'Reseller Installer'],
        ['id' => 16, 'name' => Role::ROLE_CLIENT_INSTALLER, 'team' => Team::TEAM_CLIENT, 'displayName' => 'Client Installer'],
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
     */
    public function load(ObjectManager $manager): void
    {
        $manager = $this->prepareEntityManager($manager);
        foreach (array_merge(self::ROLES_ADMIN_TEAM, self::ROLES_CLIENT_TEAM, self::ROLES_RESELLER_TEAM) as $roleData) {
            $role = $manager->getRepository(Role::class)->findOneBy(
                [
                    'name' => $roleData['name'],
                    'team' => $roleData['team']
                ]
            );

            if (!$role) {
                $role = new Role($roleData);
                $role->setId($roleData['id']);
                $manager->persist($role);
            }

            $this->setReference($roleData['name'] . '_' . $roleData['team'], $role);
        }
        $manager->flush();
    }
}
