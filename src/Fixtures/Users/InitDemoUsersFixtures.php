<?php

namespace App\Fixtures\Users;

use App\Entity\Client;
use App\Entity\Plan;
use App\Entity\Role;
use App\Entity\Team;
use App\Entity\TimeZone;
use App\Entity\User;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use App\Fixtures\InitTimezonesFixtures;
use App\Fixtures\Permissions\InitPermissionsFixture;
use App\Fixtures\Plans\InitPlansFixture;
use App\Fixtures\Roles\InitRolesFixture;
use App\Fixtures\Teams\InitTeamsFixture;
use App\Service\User\PasswordService;
use App\Service\User\VerificationService;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class InitDemoUsersFixtures extends BaseFixture implements DependentFixtureInterface, FixtureGroupInterface
{
    /** @var UserPasswordHasherInterface */
    private $encoder;

    public const SUPER_ADMIN_EMAIL = 'linxio-dev@ocsico.com';
    public const SUPER_ADMIN_PASS = 'tesT!1password';

    public const CLIENT_MANAGER_PASS = 'mS2)uT7)';

    public const CLIENT_NAME_ACME = 'ACME1';

    public const DRIVER_USERS = [
        [
            'name' => 'Nikki',
            'surname' => 'Burns',
        ],
        [
            'name' => 'Dennis',
            'surname' => 'Phillips',
        ],
        [
            'name' => 'Ian',
            'surname' => 'Dickson',
        ],
        [
            'name' => 'Robert',
            'surname' => 'Turner',
        ],
        [
            'name' => 'Celeste',
            'surname' => 'Harmer',
        ],
        [
            'name' => 'David',
            'surname' => 'White',
        ],
        [
            'name' => 'Mike',
            'surname' => 'De-Vault',
        ],
        [
            'name' => 'Graeme',
            'surname' => 'Hawkins',
        ],
        [
            'name' => 'Sue',
            'surname' => 'Baker',
        ],
        [
            'name' => 'Gerard',
            'surname' => 'Verge',
        ],
        [
            'name' => 'Narrelle',
            'surname' => 'Mamas',
        ],
        [
            'name' => 'Sharon',
            'surname' => 'Hor',
        ],
        [
            'name' => 'Lyndon',
            'surname' => 'Horwood',
        ],
        [
            'name' => 'Shane',
            'surname' => 'Burns',
        ],
        [
            'name' => 'Solbox',
            'surname' => 'Driver',
        ],
        [
            'name' => 'Alan',
            'surname' => 'Harrison',
        ],
    ];

    private const ADMIN_USERS = [
        [
            'email' => self::SUPER_ADMIN_EMAIL,
            'name' => 'Super',
            'surname' => 'Admin',
            'phone' => '+0452096181',
            'status' => User::STATUS_ACTIVE,
            'role' => [Role::ROLE_SUPER_ADMIN, Team::TEAM_ADMIN],
            'password' => self::SUPER_ADMIN_PASS,
        ],
        [
            'email' => 'linxio-admin@ocsico.com',
            'name' => 'Admin',
            'surname' => 'Admin',
            'phone' => '+0452096182',
            'status' => User::STATUS_ACTIVE,
            'role' => [Role::ROLE_ADMIN, Team::TEAM_ADMIN],
            'password' => self::SUPER_ADMIN_PASS,
        ],
        [
            'email' => 'admin.client.manager_01@linxio.local',
            'name' => 'Admin',
            'surname' => 'Client Manager #1',
            'phone' => '+555576412',
            'status' => User::STATUS_ACTIVE,
            'role' => [Role::ROLE_SALES_REP, Team::TEAM_ADMIN],
            'password' => self::CLIENT_MANAGER_PASS,
        ],
        [
            'email' => 'admin.client.manager_02@linxio.local',
            'name' => 'Admin',
            'surname' => 'Client Manager #2',
            'phone' => '+0452096183',
            'status' => User::STATUS_ACTIVE,
            'role' => [Role::ROLE_SALES_REP, Team::TEAM_ADMIN],
            'password' => self::CLIENT_MANAGER_PASS,
        ],
        [
            'email' => 'linxio-installer@ocsico.com',
            'name' => 'Admin',
            'surname' => 'Installer',
            'phone' => '+0452096184',
            'status' => User::STATUS_ACTIVE,
            'role' => [Role::ROLE_INSTALLER, Team::TEAM_ADMIN],
            'password' => self::SUPER_ADMIN_PASS,
        ],
        [
            'email' => 'linxio-support@ocsico.com',
            'name' => 'Admin',
            'surname' => 'Support',
            'phone' => '+0452096185',
            'status' => User::STATUS_ACTIVE,
            'role' => [Role::ROLE_SUPPORT, Team::TEAM_ADMIN],
            'password' => self::SUPER_ADMIN_PASS,
        ],
    ];

    private const QUATITY_CLIENTS = 9;

    private const DEFAULT_CLIENT_STATUS = Client::STATUS_CLIENT;

    private const DEFAULT_CLIENT_PLAN = Plan::PLAN_PLUS;

    public static function getGroups(): array
    {
        return [FixturesTypes::TESTING];
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
     * SuperAdminFixtures constructor.
     * @param UserPasswordHasherInterface $encoder
     */
    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @param ObjectManager $objectManager
     * @throws \Exception
     */
    public function load(ObjectManager $objectManager): void
    {
        $objectManager = $this->prepareEntityManager($objectManager);

        /** create Admin users */
        $clientManager = $this->persistAdminUsers($objectManager);

        $contactUsers = $this->persistContactUsers($objectManager);

        $clients = $this->persistClients($objectManager, $clientManager, $contactUsers);
        $this->persistClientUsers($objectManager, $clients);
        $this->persistDriverUsers($objectManager);

        $objectManager->flush();
    }

    /**
     * @param ObjectManager $objectManager
     * @return array
     * @throws \Exception
     */
    protected function persistContactUsers(ObjectManager $objectManager): array
    {
        $users = [];
        for ($i = 0; $i < self::QUATITY_CLIENTS; $i++) {
            $listUsers[$i] = [
                'email' => sprintf('client-contact-%d@ocsico.com', $i),
                'name' => sprintf('client-contact-name-%d', $i),
                'surname' => sprintf('client-surname-name-%d', $i),
                'phone' => sprintf('+012345678%d', $i),
                'role' => $objectManager->getRepository(Role::class)->findOneBy([
                    'name' => Role::ROLE_ADMIN,
                    'team' => Team::TEAM_CLIENT
                ]),
                'status' => User::STATUS_ACTIVE,
            ];
        }
        $listUsers[] = [
            'email' => 'acme-admin@linxio.local',
            'name' => 'Acme',
            'surname' => 'Admin',
            'position' => '111',
            'phone' => '+55501234',
            'status' => User::STATUS_ACTIVE,
            'role' => $objectManager->getRepository(Role::class)->findOneBy([
                'name' => Role::ROLE_ADMIN,
                'team' => Team::TEAM_CLIENT
            ]),
        ];

        foreach ($listUsers as $id => $userData) {
            $user = $objectManager->getRepository(User::class)->findOneBy([
                'email' => $userData['email']
            ]);
            if (!$user) {
                $user = new User($userData);
                $user->setPassword($this->encoder->hashPassword($user, self::CLIENT_MANAGER_PASS));
                $user->setVerifyToken(VerificationService::generateVerifyToken());
                $objectManager->persist($user);
            }
            $users[] = $user;
        }

        return $users;
    }

    /**
     * @param ObjectManager $objectManager
     * @param User $manager
     * @param User[] $contactUsers
     * @return Client[]
     */
    protected function persistClients(ObjectManager $objectManager, User $manager, array $contactUsers): array
    {
        $plan = $this->getReference(self::DEFAULT_CLIENT_PLAN);
        /** @var TimeZone $tz */

        $clients = [];
        for ($i = 0; $i < self::QUATITY_CLIENTS; $i++) {
            $listClients[$i] = [
                'name' => sprintf('client-name-%d', $i),
                'legalName' => sprintf('client-legalName-%d', $i),
                'manager' => $manager,
                'status' => self::DEFAULT_CLIENT_STATUS,
                'plan' => $plan,
                'taxNr' => '12345678910',
                'legalAddress' => 'Fictional street 11',
            ];
        }
        $listClients[] = [
            'name' => self::CLIENT_NAME_ACME,
            'legalName' => 'ACME Ltd',
            'legalAddress' => 'Fictional street 12',
            'taxNr' => '12312312312',
            'manager' => $manager,
            'status' => self::DEFAULT_CLIENT_STATUS,
            'plan' => $plan,
        ];

        foreach ($listClients as $id => $client) {
            $clientObj = $objectManager->getRepository(Client::class)->findOneBy([
                'name' => $client['name']
            ]);
            if (!$clientObj) {
                $team = Team::createNewClientTeam();

                $clientObj = new Client($client);
                $clientObj->setTeam($team);
                $clientObj->setKeyContact($contactUsers[$id]);
                $contactUsers[$id]->setTeam($team);
                $team->setClient($clientObj);
                $objectManager->persist($clientObj);
                $objectManager->persist($team);
                $clients[] = $clientObj;
            }

            $this->setReference($client['name'], $clientObj);
        }

        return $clients;
    }

    /**
     * @param ObjectManager $objectManager
     * @param array $clientUsers
     * @throws \Exception
     */
    protected function persistClientUsers(ObjectManager $objectManager, array $clientUsers): void
    {
        foreach ($clientUsers as $k => $client) {
            $user = new User([
                'email' => sprintf('client-user-%d@ocsico.com', $k),
                'name' => sprintf('client-user-name-%d', $k),
                'surname' => sprintf('client-user-name-%d', $k),
                'phone' => sprintf('+011234567%d', $k),
                'role' => $objectManager->getRepository(Role::class)->findOneBy([
                    'name' => Role::ROLE_CLIENT_ADMIN,
                    'team' => Team::TEAM_CLIENT
                ]),
                'status' => User::STATUS_ACTIVE,
            ]);

            $clientUser = $objectManager->getRepository(User::class)->findOneBy([
                'email' => $user->getEmail()
            ]);
            if (!$clientUser) {
                $user->setPassword($this->encoder->hashPassword($user, self::CLIENT_MANAGER_PASS));
                $user->setVerifyToken(VerificationService::generateVerifyToken());
                $user->setTeam($client->getTeam());
                $objectManager->persist($user);
            }
        }
    }

    /**
     * @param ObjectManager $objectManager
     * @return object
     * @throws \Exception
     */
    protected function persistAdminUsers(ObjectManager $objectManager): object
    {
        /** @var User $clientManager */
        $clientManager = null;
        foreach (self::ADMIN_USERS as $dataUser) {
            $adminUser = $objectManager->getRepository(User::class)->findOneBy([
                'email' => $dataUser['email']
            ]);
            if (!$adminUser) {
                $adminUser = new User($dataUser);
                $adminUser->setPassword(
                    $this->encoder->hashPassword(
                        $adminUser,
                        (isset($dataUser['password']))
                            ? $dataUser['password']
                            : PasswordService::generatePassword()
                    )
                );
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
            if (null === $clientManager && Role::ROLE_SALES_REP === $dataUser['role'][0]) {
                $clientManager = $adminUser;
            }
        }

        return $clientManager;
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $objectManager
     *
     * @throws \Exception
     */
    protected function persistDriverUsers(ObjectManager $objectManager)
    {
        $client = $this->getReference(self::CLIENT_NAME_ACME);
        foreach (self::DRIVER_USERS as $driver) {
            $user = new User([
                'email' => sprintf("%s.%s", $driver["name"], $driver["surname"]) . '@acme.local',
                'name' => $driver["name"],
                'surname' => $driver["surname"],
                'phone' => '+0123456789',
                'status' => User::STATUS_ACTIVE,
                'role' => $objectManager->getRepository(Role::class)->findOneBy([
                    'name' => Role::ROLE_CLIENT_DRIVER,
                    'team' => Team::TEAM_CLIENT
                ]),
            ]);

            $driverUser = $objectManager->getRepository(User::class)->findOneBy([
                'email' => $user->getEmail()
            ]);
            if (!$driverUser) {
                $user->setPassword($this->encoder->hashPassword($user, self::CLIENT_MANAGER_PASS));
                $user->setVerifyToken(VerificationService::generateVerifyToken());
                $user->setTeam($client->getTeam());
                $objectManager->persist($user);
            }
        }
    }
}
