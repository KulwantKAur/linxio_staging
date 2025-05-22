<?php

namespace App\Tests\Behat\Context\Traits;

use App\Entity\Otp;
use App\Entity\User;
use App\Enums\EntityHistoryTypes;
use App\EventListener\Notification\NotificationListener;
use App\Events\Notification\NotificationEvent;
use App\Repository\Notification\EventRepository;
use App\Service\EventLog\EventLogService;
use Behatch\Json\Json;
use Doctrine\ORM\EntityManager;
use Mockery\MockInterface;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait UsersTrait
{
    /**
     * @var Json
     */
    protected $clientData = null;
    protected $managerId = null;
    protected $clients = [];
    protected $refreshToken = null;

    /**
     * @When I want register client with manager and remember
     */
    public function iWantRegisterClientWithManagerAndRemember()
    {
        $this->fillData['manager'] = $this->managerId;
        $this->post('/api/clients', $this->fillData);

        $this->clientData = json_decode($this->getResponse()->getContent());
        $this->clientId = $this->clientData->id;
        $this->clients[] = $this->clientData;
    }

    /**
     * @When I want register
     */
    public function iWantRegister()
    {
        $this->post('/api/register', $this->fillData, ['CONTENT_TYPE' => 'multipart/form-data'], $this->files);
    }

    /**
     * @When I want verify otp :code
     */
    public function iWantVerifyOtp($code)
    {
        $this->post(
            '/api/login/otp',
            [
                'email' => $this->fillData['email'],
                'code' => $code,
                'deviceId' => $this->fillData['deviceId'] ?? null
            ]
        );

        $this->token = $this->getResponseData()['token'];
    }

    /**
     * @When I want verify otp with expired code :code
     */
    public function iWantVerifyOtpWithExpiredCode($code)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $otp = $em->getRepository(Otp::class)->findOneByEmail($this->fillData['email']);

        if ($otp) {
            $otpTtl = $this->getContainer()->getParameter('otp_ttl');
            $oldDate = (new \DateTime())->sub(new \DateInterval('PT' . ($otpTtl + 1) . 'S'));
            $otp->setCreatedAt($oldDate);
            $em->flush();
        }

        $this->post(
            '/api/login/otp',
            [
                'email' => $this->fillData['email'],
                'code' => $code,
            ]
        );

        $this->token = $this->getResponseData()['token'];
    }

    /**
     * @When I want refresh token
     */
    public function iWantRefreshToken()
    {
        $this->post(
            '/api/token/refresh',
            [
                'refreshToken' => $this->refreshToken
            ]
        );

        $this->token = $this->getResponseData()['token'];
        $this->refreshToken = $this->getResponseData()['refreshToken'];
    }

    /**
     * @When I want check token
     */
    public function iWantCheckToken()
    {
        $this->authorized($this->token);
        $this->get('/api/');
    }

    /**
     * @When I want login with wrong token
     */
    public function iWantLoginWithWrongToken()
    {
        $this->authorized('testwrongtoken');
        $this->get('/api/');
    }

    /**
     * @When I want login :email :password
     */
    public function iWantLogin($email, $password)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $em->clear();

        $data = [
            'email' => $email,
            'password' => $password,
            'deviceId' => $this->fillData['deviceId'] ?? null,
        ];
        $this->post('/api/login', $data);
    }

    /**
     * @When I want login without 2FA :email :password
     */
    public function iWantLoginWithout2FA($email, $password)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        $user->setIs2FAEnabled(false);

        $em->flush();

        $data = [
            'email' => $email,
            'password' => $password,
        ];
        $this->post('/api/login', $data);
        $this->token = $this->getResponseData()['token'];
        $this->refreshToken = $this->getResponseData()['refreshToken'];
    }

    /**
     * @When I want upload avatar
     */
    public function iWantUploadAvatar()
    {
        $this->files['picture'] = new UploadedFile(
            '/srv/features/files/test_image.png',
            'test_file.png',
            'image/png',
            UPLOAD_ERR_OK,
            true
        );
    }

    /**
     * @When I want upload file
     */
    public function iWantUploadFile()
    {
        $this->files[] = new UploadedFile(
            '/srv/features/files/test_image.png',
            'test_file.png',
            'image/png',
            UPLOAD_ERR_OK,
            true
        );
    }

    /**
     * @When I want upload file to field files
     */
    public function iWantUploadFileField()
    {
        $this->files['files'][] = new UploadedFile(
            '/srv/features/files/test_image.png',
            'test_file.png',
            'image/png',
            UPLOAD_ERR_OK,
            true
        );
    }

    /**
     * @When I want get current user info
     */
    public function iWantGetMyInfo()
    {
        $this->get('/api/me');
    }

    /**
     * @When I want register client manager user and remember id
     */
    public function iWantRegisterClientManagerUser()
    {
        $response = $this->post('/api/users/admin', $this->fillData);
        $this->managerId = json_decode($response->getResponse()->getContent())->id;
    }

    /**
     * @When I want get user by id with email :email
     */
    public function iWantGetUserByIdWithEmail(string $email)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var User $user */
        $user = $em->getRepository(User::class)->findOneByEmail($email);

        $this->get(sprintf('/api/users/%d', $user->getId()));
    }

    /**
     * @When I want get users
     */
    public function iWantGetUsers()
    {
        $this->get('/api/users');
    }

    /**
     * @When I want update user data by id with email :email
     */
    public function iWantUpdateUserDataByIdWithEmail(string $email)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var User $user */
        $user = $em->getRepository(User::class)->findOneByEmail($email);

        $this->patch(sprintf('/api/users/%d', $user->getId()), $this->fillData);
    }

    /**
     * @When I want delete admin team user by id with email :email
     */
    public function iWantDeleteAdminUserByIdWithEmail(string $email)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var User $user */
        $user = $em->getRepository(User::class)->findOneByEmail($email);

        $this->delete(sprintf('/api/admin/users/%d', $user->getId()), $this->fillData);
    }

    /**
     * @When I want restore user by id with email :email
     */
    public function iWantRestoreAdminUserByIdWithEmail(string $email)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var User $user */
        $user = $em->getRepository(User::class)->findOneByEmail($email);

        $this->post(sprintf('/api/users/%d/restore', $user->getId()));
    }

    /**
     * @When I want update admin team user data by id with email :email
     */
    public function iWantUpdateAdminTeamUserDataByIdWithEmail(string $email)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var User $user */
        $user = $em->getRepository(User::class)->findOneByEmail($email);

        $this->post(sprintf('/api/admin/users/%d', $user->getId()), $this->fillData);
    }

    /**
     * @When I want add manager :email saved clients
     */
    public function iWantAddManagerSavedClients(string $email)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var User $user */

        $user = $em->getRepository(User::class)->findOneByEmail($email);

        foreach ($this->clients as $client) {
            $this->fillData['teams'][] = $client->team->id;
        }

        $this->patch(sprintf('/api/admin/users/%d/teams', $user->getId()), $this->fillData);
    }

    /**
     * @When I want get admin team user data by id with email :email
     */
    public function iWantGetAdminTeamUserDataByIdWithEmail(string $email)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var User $user */
        $user = $em->getRepository(User::class)->findOneByEmail($email);

        $this->get(sprintf('/api/admin/users/%d', $user->getId()));
    }

    /**
     * @When I want set :field field with :value for user with email :email
     */
    public function iWantSetUserField(string $field, $value, string $email)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var User $user */
        $user = $em->getRepository(User::class)->findOneByEmail($email);

        $setter = sprintf('set%s', ucfirst($field));

        if (!method_exists($user, $setter)) {
            throw new \Exception(sprintf('Setter \'%s::%s()\', does not exist.', get_class($user), $setter));
        }

        $user->$setter($value);

        $em->flush();
    }

    /**
     * @When I want to know user :email exists in entity history
     */
    public function iWantToKnowUserExistInEntityHistory($email)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        $createdHistory = $this->getContainer()->get('app.entity_history_service')->getByEntityIdAndType(
            $user->getId(),
            EntityHistoryTypes::USER_CREATED
        );
        if (!$createdHistory) {
            throw new \Exception('Create history is not found');
        }
    }

    /**
     * @When I want get user update history :email
     */
    public function iWantGetUserUpdateHistory($email)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        $this->get('/api/users/' . $user->getId() . '/history/updated');
    }

    /**
     * @When I want get user last login history :email
     */
    public function iWantGetUserLastLoginHistory($email)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        $this->get('/api/users/' . $user->getId() . '/history/last-login');
    }

    /**
     * @When I want send verification code to user with email :email
     */
    public function iWantSendVerificationCodeToUser($email)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        $this->fillData['token'] = $user->getVerifyToken();

        $this->post('/api/phone/code', $this->fillData);
    }

    /**
     * @When I want verify phone :phone with code :code
     */
    public function iWantVerifyPhone($phone, $code)
    {
        $this->post('/api/phone/verify', ['phone' => $phone, 'code' => $code]);
    }

    /**
     * @When I want verify user phone by email :email
     */
    public function iWantVerifyUserPhone($email)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var User $user */
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        $user->verifyPhone();
        $em->flush();
    }

    /**
     * @When I want get phone user with email :email by token
     */
    public function iWantGetPhoneByToken($email)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        $this->post('/api/phone', ['token' => $user->getVerifyToken()]);
    }

    /**
     * @When I want set user with email :email password :password by token
     */
    public function iWantSetUserPassword($email, $password)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        $this->post('/api/password/set', ['token' => $user->getVerifyToken(), 'password' => $password]);
    }

    /**
     * @When I want export admin team list
     */
    public function iWantExportAdminTeamList()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/users/csv?' . $params);
    }

    /**
     * @When I want export users list by teamId
     */
    public function iWantExportUsersListByTeamId()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/users/csv?' . $params);
    }

    /**
     * @When I want check verify token :token
     */
    public function iWantCheckVerifyToken($token)
    {
        $this->post('/api/users/check-verify-token', ['token' => $token]);
    }

    /**
     * @When  I want check verify token for user :email
     */
    public function iWantCheckVerifyTokenForUser($email)
    {
        /** @var User $user */
        $user = $this->getEntityManager()->getRepository(User::class)->findOneBy(['email' => $email]);
        $this->post('/api/users/check-verify-token', ['token' => $user->getVerifyToken()]);
    }

    /**
     * @When  I want change createAt for user :email :modify
     */
    public function iWantChangeCreateAtForUser($email, $modify)
    {
        /** @var User $user */
        $user = $this->getEntityManager()->getRepository(User::class)->findOneBy(['email' => $email]);
        $user->getCreatedAt()->modify($modify);
        $this->getEntityManager()->flush();
    }

    /**
     * @When I want handle notification event :eventName :userEmail
     */
    public function iWantSet($eventName, $userEmail)
    {
        /** @var EventLogService|MockInterface $eventLogService */
        $eventLogService = \Mockery::mock(EventLogService::class)->makePartial();

        $mockRepo = \Mockery::mock(EventRepository::class);
        $mockRepo->shouldReceive('findBy')
            ->withArgs(
                static function ($search) use ($eventName) {
                    return is_array($search)
                        && isset($search['name'])
                        && $search['name'] === $eventName;
                }
            )
            ->andReturn([])
            ->once();

        $mockEm = \Mockery::mock(EntityManager::class);
        $mockEm->shouldReceive('getRepository')->andReturn($mockRepo);

        $listenerMock = \Mockery::mock(
            sprintf('%s[processingNotificationEvent]', NotificationListener::class),
            [$mockEm, \Mockery::mock(Producer::class), $eventLogService]
        )->makePartial();
        $listenerMock->shouldReceive('processingNotificationEvent')
            ->withArgs(
                static function ($event) use ($eventName, $userEmail) {
                    /** @var NotificationEvent $event */
                    return is_object($event) &&
                        ($event instanceof NotificationEvent) &&
                        $event->getEventName() === $eventName &&
                        $event->getEntity()->getEmail() === $userEmail;
                }
            )
            ->once();

        $this->getKernel()->getContainer()->set(NotificationListener::class, $listenerMock);
    }

    /**
     * @When I want get driver list
     */
    public function iWantGetDriverList()
    {
        $params = http_build_query(['fields' => ['vehicle']]);
        $this->get('/api/drivers?' . $params);
    }

    /**
     * @When I want send support message with body :body
     */
    public function iWantSendSupportMessage($body)
    {
        $this->post('/api/support/request', ['body' => $body], ['CONTENT_TYPE' => 'multipart/form-data'], $this->files);
    }
}
