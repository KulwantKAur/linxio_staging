<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context;

use App\Entity\Client;
use App\Entity\Plan;
use App\Entity\Role;
use App\Entity\Team;
use App\Entity\Theme;
use App\Entity\User;
use App\EntityManager\SlaveEntityManager;
use App\Service\User\PasswordService;
use App\Tests\Behat\Support\DoctrineHelperTrait;
use App\Tests\Behat\Support\KernelDictionary;
use App\Tests\Behat\Support\RequestTrait;
use App\Tests\Behat\Support\ResponseTrait;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behatch\Json\Json;
use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

abstract class BasicContext implements Context
{
    use ResponseTrait;
    use KernelDictionary;
    use DoctrineHelperTrait;
    use RequestTrait;

    protected $fillData = [];
    protected $token;
    protected $files = [];
    protected $authorizedUser;
    protected $entityArray;
    protected $clientId;
    protected $serializer;
    protected $filePath = '/srv/features/files/';
    protected $clientData;
    protected $exitCode;
    protected $output;
    protected $teamId;

    /** @var LoggerInterface  */
    protected $logger;

    /** @var SlaveEntityManager */
    protected $slaveEntityManager;

    /** @var EntityManagerInterface  */
    protected $em;

    public function __construct(
        KernelInterface $kernel,
        LoggerInterface $logger,
        EntityManagerInterface $em,
        SlaveEntityManager $slaveEntityManager
    ) {
        $this->kernel = $kernel;
        $this->logger = $logger;
        $this->slaveEntityManager = $slaveEntityManager;
        $this->em = $em;
        $this->serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
//        $this->serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder(',', '"', '\\', '/')]);
    }

    /**
     * @When I want to get access to server
     */
    public function iWantToGetAccessToServer()
    {
        return $this->get('/api/health');
    }

    /**
     * @When I want fill :field field with :value
     */
    public function iFillData($field, $value)
    {
        if ($value === 'true') {
            $value = true;
        } elseif ($value === 'false') {
            $value = false;
        } elseif ($value === 'null') {
            $value = null;
        }

        $fieldLink = &$this->fillData;
        foreach (explode('.', $field) as $subField) {
            if (empty($subField) && !is_numeric($subField)) {
                $fieldLink[] = $value;
                return;
            }

            $fieldLink[$subField] = $fieldLink[$subField] ?? [];
            $fieldLink = &$fieldLink[$subField];
        }

        $fieldLink = $value;
    }

    /**
     * @When I want fill bool field :field with text value :value
     */
    public function iWantFillBoolField($field, $value)
    {
        $this->fillData[$field] = $value;
    }

    /**
     * @When I want fill teamId with saved team id
     */
    public function iWantFillTeamId()
    {
        $this->fillData['teamId'] = $this->teamId;
    }

    /**
     * @When I want fill date field :field with now
     */
    public function iWantFillDateNow($field)
    {
        $this->fillData[$field] = (new \DateTime())->format('c');
    }

    /**
     * @When I want fill date field :field with tomorrow
     */
    public function iWantFillDateTomorrow($field)
    {
        $this->fillData[$field] = (new \DateTime())->add(new \DateInterval('P1D'));
    }

    /**
     * @When I want fill date field :field with :days days ago
     */
    public function iWantFillDateYesterday($field, $days)
    {
        $this->fillData[$field] = (new \DateTime())->sub(new \DateInterval('P' . $days . 'D'))->format('c');
    }

    /**
     * @When I want clean filled data
     */
    public function iWantCleanFilledData()
    {
        $this->fillData = [];
    }


    /**
     * @When I want fill :field field with json: :json
     * @param $field
     * @param string $json
     */
    public function iFillDataJson($field, string $json)
    {
        $this->fillData[$field] = (new Json($json))->getContent();
    }

    /**
     * @When I want to fill params with json: :json
     * @param string $json
     */
    public function iWantToFillParamsWithJson(string $json)
    {
        $this->fillData = $json;
    }

    /**
     * @When I want fill array key :key with :field field with :value
     */
    public function iFillDataArray($key, $field, $value)
    {
        if ($value === 'date::now') {
            $value = (new \DateTime())->format('c');
        } elseif ($value === 'date::tomorrow') {
            $value = (new \DateTime())->add(new \DateInterval('P1D'))->format('c');
        }
        if (isset($this->fillData[$key])) {
            $this->fillData[$key][$field] = $value;
        } else {
            $this->fillData[$key] = [$field => $value];
        }
    }

    /**
     * @When I want add value to array key :key with :value
     */
    public function iAddDataArray($key, $value)
    {
        $this->fillData[$key][] = $value;
    }

    /**
     * @When I want fill key :key with empty array
     */
    public function iFillKeyEmptyArray($key)
    {
        $this->fillData[$key] = [];
    }

    /**
     * @When I want check :field is not empty
     */
    public function iWantCheckFieldNotEmpty($field)
    {
        return (bool)$field;
    }

    /**
     * @Given I signed in
     */
    public function iSignedIn(): void
    {
        $this->iSignedAs('super_admin');
    }

    /**
     * @Given I signed in as :role team :team
     */
    public function iSignedAs($role, $team = Team::TEAM_ADMIN)
    {
        $this->authorizedUser = $this->createUser($role, $team);
        $this->generateTokenFor($this->authorizedUser);
    }

    /**
     * @Given I signed in as :role team :team and teamId :teamId
     */
    public function iSignedAsTeamIdUser($role, $team = Team::TEAM_ADMIN, $teamId = null)
    {
        $this->authorizedUser = $this->createUser($role, $team, $teamId);
        $this->generateTokenFor($this->authorizedUser);
    }

    /**
     * @Given I signed with email :email
     */
    public function iSignedWithEmail($email)
    {
        $em = $this->getEntityManager();
        $this->authorizedUser = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        $this->generateTokenFor($this->authorizedUser);
    }

    /**
     * @param $roleName
     * @param string $team
     *
     * @param null $teamId
     *
     * @return User
     */
    protected function createUser($roleName, $team = Team::TEAM_ADMIN, $teamId = null): User
    {
        $email = $roleName . '@user.com';
        $em = $this->getEntityManager();
        if ($user = $em->getRepository(User::class)->findOneBy(['email' => $email])) {
            return $user;
        }
        $user = new User(
            [
                'email' => $email,
                'name' => 'test user',
                'phone' => '+467662616',
                'surname' => 'surname',
            ]
        );
        $user->setPassword(PasswordService::generatePassword());
        $role = $em->getRepository(Role::class)->findOneBy(['name' => $roleName, 'team' => $team]);
        $user->setRole($role);
        switch ($team) {
            case Team::TEAM_ADMIN:
                $user->setTeam($this->getAdminTeam());
                break;
            case Team::TEAM_CLIENT:
                $team = $this->getClientTeam($teamId);
                $user->setTeam($team);
                break;
        }
        if ($user->isControlAdmin()) {
            $user->setAllTeamsPermissions(true);
        }
        $user->verifyPhone();
        $em->persist($user);
        $em->flush();

        return $user;
    }

    /**
     * @return EntityManagerInterface
     */
    protected function getEntityManager()
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        if (!$em->isOpen()) {
            $em = $em->create($em->getConnection(), $em->getConfiguration());
        }
        return $em;

//        if (!$this->em->isOpen()) {
//            return $this->em->create($this->em->getConnection(), $this->em->getConfiguration());
//        }
//        return $this->em;
    }

    /**
     * @param User $user
     */
    protected function generateTokenFor(User $user): void
    {
        $token = $this->getContainer()->get('lexik_jwt_authentication.jwt_manager')->create($user);
        $this->authorized($token);

        $token = new UsernamePasswordToken($user, null, 'api', $user->getRoles());
        $this->getKernel()->getContainer()->get('security.token_storage')->setToken($token);
    }

    /**
     * @Given Elastica populate
     * @param string|null $index
     */
    public function elasticSearchPopulate(?string $index = null)
    {
        $application = new Application($this->getKernel());
        $application->setAutoExit(false);
        $commandParams = $index ? ' --index=' . $index : '';

        $input = new ArrayInput(
            [
                'command' => 'fos:elastica:populate' . $commandParams
            ]
        );

        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput();
        $application->run($input, $output);
    }

    /**
     * @Given Elastica populate index :index
     * @param string $index
     */
    public function elasticSearchPopulateIndex(string $index)
    {
        $this->elasticSearchPopulate($index);
    }

    /**
     * @Given insert Procedures
     */
    public function insertProcedures()
    {
        $application = new Application($this->getKernel());
        $application->setAutoExit(false);

        $input = new ArrayInput(
            [
                'command' => 'db:procedures:insert'
            ]
        );

        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput();
        $application->run($input, $output);
    }

    /**
     * @Given Run Fixture
     */
    public function runFixture()
    {
        $application = new Application($this->getKernel());
        $application->setAutoExit(false);

        $input = new ArrayInput(
            [
                'command' => 'doctrine:fixtures:load',
                '--purge-with-truncate' => true,
                '--no-interaction' => true
            ]
        );

        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput();
        $application->run($input, $output);

        sleep(3);
    }

    /**
     * @Given Load Fixture :fixture
     */
    public function loadFixture(string $fixture)
    {
        /** @var ObjectManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $fixtureClass = sprintf('App\\Fixtures\\%s', $fixture);

        if (class_exists($fixtureClass)) {
            /** @var ORMFixtureInterface|AbstractFixture $fixture */
            $fixture = new $fixtureClass;
            $fixture->setReferenceRepository(new ReferenceRepository($this->getEntityManager()));

            $fixture->load($em);
        } else {
            throw new \Exception('Invalid fixture name');
        }
    }

    /**
     * @Then I do not see field :field
     */
    public function iNotSeeField($field)
    {
        try {
            $this->iSeeField($field);
        } catch (\Exception $e) {
            return true;
        }

        throw new \Exception('Field exist' . $this->getResponse()->getContent());
    }

    /**
     * @When I want sleep on :sec seconds
     */
    public function iWantSleepOn($seconds = 1)
    {
        sleep($seconds);
    }

    /**
     * @When I want get clients list
     */
    public function iWantGetClientsList()
    {
        $params = http_build_query($this->fillData);
        return $this->get('/api/clients/json?' . $params);
    }

    /**
     * @When I want register client
     */
    public function iWantRegisterClient()
    {
        return $this->post('/api/clients', $this->fillData);
    }

    /**
     * @When I see in DB field :field filled with :value for user with :email
     */
    public function iSeeInDbFieldFilledWithValueForUserWithEmail(string $field, $value, string $email)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var User $user */
        $user = $em->getRepository(User::class)->findOneByEmail($email);

        $getter = sprintf('get%s', ucfirst($field));

        $fieldValue = $user->$getter();

        if ($fieldValue !== $value) {
            throw new \Exception(sprintf('Value filled \'%s\'', $fieldValue));
        }
    }

    /**
     * @When I want set remembered team settings :settingName for role :roleName :team with value :value
     */
    public function iWantSetRememberedTeamSettingsForRoleWithValue($roleName, $settingName, $value, $team)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $roleId = $em->getRepository(Role::class)->findOneBy(['name' => $roleName, 'team' => $team])->getId();
        $id = $this->clientData->team->id;

        if ('theme' === $settingName) {
            $value = $em->getRepository(Theme::class)->findOneBy(['alias' => $value])->getId();
        }
        $data = [['roleId' => $roleId, 'name' => $settingName, 'value' => $value]];

        $this->patch('/api/settings/team/' . $id, $data);
    }

    /**
     * @When I want set remembered team settings :settingName for role :roleName :team with raw value
     */
    public function iWantSetRememberedTeamSettingsForRoleWithRawValue(
        $roleName,
        $settingName,
        $team,
        PyStringNode $value
    ) {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $roleId = $em->getRepository(Role::class)->findOneBy(['name' => $roleName, 'team' => $team])->getId();
        $id = $this->clientData->team->id;

        if ('theme' === $settingName) {
            $value = $em->getRepository(Theme::class)->findOneBy(['alias' => $value])->getId();
        } else {
            $value = json_decode($value, true);
        }

        $data = [
            [
                'roleId' => $roleId,
                'name' => $settingName,
                'value' => $value,
            ],
        ];
        $this->patch('/api/settings/team/' . $id, $data);
    }

    /**
     * @When I want set remembered team settings :settingName with raw value
     */
    public function iWantSetRememberedTeamSettingsWithRawValue($settingName, PyStringNode $value)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $id = $this->clientData->team->id;

        if ('theme' === $settingName) {
            $value = $em->getRepository(Theme::class)->findOneBy(['alias' => $value])->getId();
        } else {
            $value = json_decode($value, true);
        }

        $data = [
            [
                'name' => $settingName,
                'value' => $value,
            ],
        ];
        $this->patch('/api/settings/team/' . $id, $data);
    }

    /**
     * @When I want set remembered team settings :settingName with value :value
     */
    public function iWantSetRememberedTeamSettingsWithValue($settingName, $value)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $id = $this->clientData->team->id;

        if ('theme' === $settingName) {
            $value = $em->getRepository(Theme::class)->findOneBy(['alias' => $value])->getId();
        }

        $data = [
            [
                'name' => $settingName,
                'value' => $value,
            ],
        ];
        $this->patch('/api/settings/team/' . $id, $data);
    }

    /**
     * @When I want set admin team with role :roleName :roleTeam setting :settingName value :value
     */
    public function iWantSetAdminSettings($roleName, $roleTeam, $settingName, $value)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $roleId = $em->getRepository(Role::class)->findOneBy(['name' => $roleName, 'team' => $roleTeam])->getId();

        $em = $this->getEntityManager();
        $adminTeam = $em->getRepository(Team::class)->findOneBy(
            [
                'type' => Team::TEAM_ADMIN
            ]
        );

        if ('theme' === $settingName) {
            $value = $em->getRepository(Theme::class)->findOneBy(['alias' => $value])->getId();
        }

        $data = [
            [
                'roleId' => $roleId,
                'name' => $settingName,
                'value' => $value,
            ],
        ];
        $this->patch('/api/settings/team/' . $adminTeam->getId(), $data);
    }

    /**
     * @When I want get remembered team settings
     */
    public function iWantGetRememberedTeamSettings()
    {
        $id = $this->clientData->team->id;
        return $this->get('/api/settings/team/' . $id);
    }

    /**
     * @When I want get admin settings
     */
    public function iWantGetAdminSettings()
    {
        $em = $this->getEntityManager();
        $adminTeam = $em->getRepository(Team::class)->findOneBy(
            [
                'type' => Team::TEAM_ADMIN,
            ]
        );
        $this->get('/api/settings/team/' . $adminTeam->getId());
    }

    /**
     * @When I want get setting by key :key
     */
    public function iWantGetSettingByKey($key)
    {
        return $this->get('/api/settings/' . $key);
    }

    /**
     * @When I want get setting by keys of array
     */
    public function iWantGetSettingByKeysOfArray()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/settings?' . $params);
    }

    /**
     * @When I want logout
     */
    public function iWantLogout()
    {
        $this->post('/api/logout');
    }

    /**
     * @When I want get client by name :name and save id
     */
    public function iWantGetClientByNameAndSaveId($name)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $client = $em->getRepository(Client::class)->findOneBy(['name' => $name]);

        $this->clientData = json_decode(json_encode($client->toArray()));
        $this->clientId = $client->getId();
    }

    /**
     * @When I want get client team by name :name and save id
     */
    public function iWantGetClientTeamByNameAndSaveId($name)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $client = $em->getRepository(Client::class)->findOneBy(['name' => $name]);

        $this->clientData = json_decode(json_encode($client->toArray()));
        $this->teamId = $client->getTeam()->getId();
    }

    /**
     * @When I want get my theme
     */
    public function iWantGetMyTheme()
    {
        $this->getEntityManager()->clear();
        $this->get('/api/themes/my');
    }

    /**
     * @return Team
     */
    public function getAdminTeam(): Team
    {
        $em = $this->getEntityManager();
        $adminTeam = $em->getRepository(Team::class)->findOneBy(
            [
                'type' => Team::TEAM_ADMIN,
            ]
        );

        if (!$adminTeam) {
            $adminTeam = new Team(['type' => Team::TEAM_ADMIN]);
            $em->persist($adminTeam);
            $em->flush();
        }

        return $adminTeam;
    }

    /**
     * @param null $id
     * @return Team
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function getClientTeam($id = null): Team
    {
        $em = $this->getEntityManager();

        if ($id) {
            $clientTeam = $this->getEntityManager()->getRepository(Team::class)->find($id);
        } else {
            $clientTeam = new Team(['type' => Team::TEAM_CLIENT]);
        }

        if ($id && $clientTeam) {
            $client = $this->getEntityManager()->getRepository(Client::class)->find($clientTeam->getClientId());
        } else {
            $client = new Client(
                [
                    'name' => sprintf('client-name-%d', time()),
                    'status' => Client::STATUS_CLIENT,
                    'team' => $clientTeam
                ]
            );
            $planPlus = $this->getEntityManager()->getRepository(Plan::class)->findOneBy(
                ['name' => Plan::PLAN_PLUS]
            );
            $client->setPlan($planPlus);
        }

        $clientTeam->setClient($client);

        $em->persist($clientTeam);
        $em->persist($client);
        $em->flush();

        return $clientTeam;
    }

    /**
     * @When I want set user password :userEmail :userPassword in DB
     */
    public function iWantSetUserPassInDb($userEmail, $userPassword)
    {
        /** @var User $user */
        $user = $this->getEntityManager()->getRepository(User::class)->findOneBy(['email' => $userEmail]);

        $passwordEncoder = $this->getContainer()->get('security.user_password_hasher');

        $user->setPassword($passwordEncoder->hashPassword($user, $userPassword));

        $this->getEntityManager()->flush();
    }

    /**
     * @When I want get event log
     */
    public function iWantGetEventLog()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/event-log/json?' . $params);

        $this->eventData = json_decode($this->getResponse()->getContent());
    }

    /**
     * @When I want login as client with name :name
     */
    public function iWantLoginAsClient($name)
    {
        $client = $this->getEntityManager()->getRepository(Client::class)->findOneBy(['name' => $name]);
        $this->post('/api/client/' . $client->getId() . '/login');
    }

    /**
     * @When I want login as user with email :email
     */
    public function iWantLoginAsUser($email)
    {
        $user = $this->getEntityManager()->getRepository(User::class)->findOneBy(['email' => $email]);
        $this->post('/api/user/' . $user->getId() . '/login');
    }

    /**
     * @When I want login with driver id
     */
    public function iWantLoginWithIdAsUser()
    {
        $this->getEntityManager()->clear();
        $this->post('/api/login-with-id', $this->fillData);
    }

    /**
     * @When I want check client :name driver id
     */
    public function iWantCheckDriverId($name)
    {
        $client = $this->getEntityManager()->getRepository(Client::class)->findOneBy(['name' => $name]);

        $params = http_build_query(
            ['teamId' => $client->getTeam()->getId(), 'driverId' => $this->fillData['driverId']]
        );
        $this->get('/api/check-driver-id?' . $params);
    }

    /**
     * @When I want set mobile device with id :id
     */
    public function iWantSetMobileDevice($id)
    {
        $this->fillData['deviceId'] = $id;

        $this->post('/api/set-mobile-device', $this->fillData);
    }

    /**
     * @When I want get mobile device with id :id
     */
    public function iWantGetMobileDevice($id)
    {
        $params = http_build_query(['deviceId' => $id]);

        $this->get('/api/get-mobile-device?' . $params);
    }

    /**
     * @When I want set mobile device token
     */
    public function iWantSetMobileDeviceToken()
    {
        $this->post('/api/set-mobile-device-token', $this->fillData);
    }


    /**
     * @When I want upload file :file :format :size :mimeType
     */
    public function iWantUploadFiles($file, $format, $size, $mimeType)
    {
        $sampleFile = $this->filePath . $file . '.' . $format;
        $testFile = $file . '_test.' . $format;
        $pathTestFile = $this->filePath . $testFile;

        if (!copy($sampleFile, $pathTestFile)) {
            throw new \Exception('Failed to copy file:' . $testFile);
        }

        $this->files[] = new UploadedFile(
            $pathTestFile,
            $testFile,
            $mimeType,
            UPLOAD_ERR_OK,
            true
        );
    }

    /**
     * @When I want clean files data
     */
    public function iWantCleanFilesData()
    {
        $this->files = [];
    }

    /**
     * @param $content
     * @param int $statusCode
     * @return $this
     */
    public function setResponse($content, $statusCode = 200)
    {
        $this->lastResponse = new class {
            public $content;
            public $statusCode;

            public function getContent()
            {
                return $this->content;
            }

            public function getStatusCode()
            {
                return $this->statusCode;
            }
        };

        $this->lastResponse->content = $content;
        $this->lastResponse->statusCode = $statusCode;

        return $this;
    }

    /**
     * @Given I want to migrate postgres functions
     */
    public function iWantToMigratePostgresFunctions()
    {
        $this->runCommand('db:procedures:insert');
    }

    /**
     * @param string $command
     *
     * @return void
     */
    protected function runCommand(string $command)
    {
        $application = new Application($this->getKernel());
        $application->setAutoExit(false);

        $input = new ArrayInput([$command]);

        $this->output = new BufferedOutput();
        $this->exitCode = $application->run($input, $this->output);
    }
}
