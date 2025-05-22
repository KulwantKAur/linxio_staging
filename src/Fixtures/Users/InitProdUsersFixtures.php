<?php

namespace App\Fixtures\Users;

use App\Entity\Role;
use App\Entity\Team;
use App\Entity\User;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use App\Fixtures\InitTimezonesFixtures;
use App\Fixtures\Permissions\InitPermissionsFixture;
use App\Fixtures\Plans\InitPlansFixture;
use App\Fixtures\Roles\InitRolesFixture;
use App\Fixtures\Teams\InitTeamsFixture;
use App\Service\User\VerificationService;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class InitProdUsersFixtures extends BaseFixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const SUPER_ADMIN_EMAIL = 'linxio-dev@ocsico.com';

    private const ADMIN_USERS = [
        [
            'email' => self::SUPER_ADMIN_EMAIL,
            'name' => 'Super',
            'surname' => 'Admin',
            'phone' => '+0452096181',
            'status' => User::STATUS_ACTIVE,
            'role' => [Role::ROLE_SUPER_ADMIN, Team::TEAM_ADMIN],
        ],
    ];

    public static function getGroups(): array
    {
        return [FixturesTypes::GLOBAL];
    }

    public function getDependencies(): array
    {
        return [
            InitPlansFixture::class,
            InitTimezonesFixtures::class,
            InitPermissionsFixture::class,
            InitRolesFixture::class,
            InitTeamsFixture::class,
        ];
    }

    /**
     * @param ObjectManager $objectManager
     * @throws \Exception
     */
    public function load(ObjectManager $objectManager): void
    {
        $objectManager = $this->prepareEntityManager($objectManager);
        $this->persistAdminUsers($objectManager);
        $objectManager->flush();
    }

    /**
     * @param ObjectManager $objectManager
     * @return void
     * @throws \Exception
     */
    protected function persistAdminUsers(ObjectManager $objectManager): void
    {
        foreach (self::ADMIN_USERS as $dataUser) {
            $adminUser = $objectManager->getRepository(User::class)->findOneBy([
                'email' => $dataUser['email']
            ]);
            if (!$adminUser) {
                $adminUser = new User($dataUser);
                $team = $this->getReference(InitTeamsFixture::ADMIN_TEAM_REFERENCE_ALIAS);
                $role = $this->getReference(implode('_', $dataUser['role']));
                $adminUser->setTeam($team);
                $adminUser->setRole($role);
                $adminUser->verifyPhone();
                $adminUser->setVerifyToken(VerificationService::generateVerifyToken());

                if ($adminUser->isControlAdmin() || $adminUser->isInstaller()) {
                    $adminUser->setAllTeamsPermissions(true);
                }

                $objectManager->persist($adminUser);
            }
        }
    }
}
