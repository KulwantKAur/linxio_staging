<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\MobileDevice;
use App\Entity\Notification\Event;
use App\Entity\Permission;
use App\Entity\PlanRolePermission;
use App\Entity\Reseller;
use App\Entity\Setting;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserDevice;
use App\Events\User\UserCreatedEvent;
use App\Service\Auth\AuthService;
use App\Service\MobileDevice\MobileDeviceService;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Notification\NotificationMobileDeviceService;
use App\Service\Sms\OtpService;
use App\Service\User\UserService;
use App\Util\DateHelper;
use Doctrine\ORM\EntityManager;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\ExpiredTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidTokenException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AuthController extends BaseController
{
    private $userService;
    private $notificationDispatcher;
    private $mobileDeviceService;
    private $notificationMobileDeviceService;
    private $translator;
    private $otpService;
    private $eventDispatcher;
    private $tokenStorage;
    private EntityManager $em;

    public function __construct(
        UserService $userService,
        NotificationEventDispatcher $notificationDispatcher,
        MobileDeviceService $mobileDeviceService,
        NotificationMobileDeviceService $notificationMobileDeviceService,
        TranslatorInterface $translator,
        OtpService $otpService,
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        EntityManager $em,
        private AuthService $authService,
    ) {
        $this->userService = $userService;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->mobileDeviceService = $mobileDeviceService;
        $this->notificationMobileDeviceService = $notificationMobileDeviceService;
        $this->translator = $translator;
        $this->otpService = $otpService;
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenStorage = $tokenStorage;
        $this->em = $em;
    }

    #[Route('/register', methods: ['POST'])]
    public function register(Request $request)
    {
        try {
            $avatarFile = $request->files->get('picture') ?? null;
            $user = $this->userService->create(
                array_merge_recursive(
                    $request->request->all(),
                    ['avatar' => $avatarFile, 'createdBy' => $this->getUser()]
                )
            );
            $this->eventDispatcher->dispatch(new UserCreatedEvent($user), UserCreatedEvent::NAME);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($user);
    }

    #[Route('/login', methods: ['POST'])]
    public function login(Request $request)
    {
        $user = $this->userService->findUserByEmail(strtolower($request->request->get('email')));

        return $this->viewItem($this->authService->login($user, $request));
    }

    #[Route('/login-with-id', methods: ['POST'])]
    public function loginWithId(Request $request)
    {
        $driverId = $request->request->get('driverId');
        $mobileDeviceId = $request->request->get('deviceId');

        if (!$driverId || !$mobileDeviceId) {
            throw new BadCredentialsException($this->translator->trans('validation.errors.field.required'));
        }

        $mobileDevice = $this->em->getRepository(MobileDevice::class)
            ->findOneBy(['deviceId' => $mobileDeviceId]);

        if (!$mobileDevice) {
            throw new BadCredentialsException($this->translator->trans('auth.mobileDevice.not_found'));
        }

        if (!$mobileDevice->getLoginWithId()) {
            throw new BadCredentialsException($this->translator->trans('auth.mobileDevice.not_allowed'));
        }

        $user = $this->em->getRepository(User::class)->findOneBy(
            [
                'driverId' => $driverId,
                'team' => $mobileDevice->getTeam()
            ]
        );

        if (!$user) {
            throw new BadCredentialsException($this->translator->trans('auth.user.not_found'));
        }

        if (!$user->isDriverClient()) {
            throw new BadCredentialsException($this->translator->trans('auth.user.loginWithId'));
        }

        if ($user->isDeleted()) {
            throw new BadCredentialsException($this->translator->trans('auth.user.deleted'));
        }

        if ($user->isBlocked()) {
            return $this->viewItem(
                [
                    'blocked' => true,
                    'message' => $user->getBlockingMessage(),
                    'teamType' => $user->getTeamType()
                ]
            );
        }

        if ($user->canLoginWithId()) {
            $mobileDevice->setLastLoggedAt(new \DateTime());
            $this->em->flush();

            return $this->viewItem(
                [
                    'token' => $this->authService->getAuthToken($user),
                    'loginWithId' => $user->canLoginWithId(),
                    'expireAt' => DateHelper::getTokenExpireAtString($this->getParameter('jwt_ttl')),
                    'teamType' => $user->getTeamType(),
                    'roleId' => $user->getRole()->getId(),
                    ...$this->authService->getRefreshTokenData($user),
                ]
            );
        } else {
            return $this->viewItem(
                [
                    'loginWithId' => $user->canLoginWithId()
                ]
            );
        }
    }

    #[Route('/login/otp', methods: ['POST'])]
    public function verifyOtp(Request $request)
    {
        $email = $request->request->get('email');
        $code = $request->request->get('code');
        $deviceId = $request->request->get('deviceId');
        if ($deviceId && $email) {
            $user = $this->userService->findUserByEmailAndDeviceId($email, $deviceId);
        }

        if (!($user ?? null)) {
            $otp = $this->otpService->findOtpByEmail($email, $code);

            $this->otpService->verify($otp);
            $user = $otp->getUser();

            if ($deviceId && $user) {
                /** @var UserDevice $userDevice */
                $userDevice = $this->em->getRepository(UserDevice::class)->findBy(
                    [
                        'user' => $user,
                        'deviceId' => $deviceId
                    ]
                );
                $attributes = ['deviceId' => $deviceId, 'user' => $user];

                if (!$userDevice) {
                    $userDevice = new UserDevice($attributes);
                    $this->em->persist($userDevice);
                }

                $this->em->flush();
            }
        }

        $token = $this->authService->getAuthToken($user);

        return $this->viewItem(
            [
                'token' => $token,
                'expireAt' => DateHelper::getTokenExpireAtString($this->getParameter('jwt_ttl')),
                'teamType' => $user->getTeamType(),
                'roleId' => $user->getRole()->getId(),
                ...$this->authService->getRefreshTokenData($user),
            ]
        );
    }

    #[Route('/', methods: ['GET'])]
    public function api()
    {
        return new JsonResponse(['email' => $this->getUser()->getEmail()]);
    }

    #[Route('/me', methods: ['GET'])]
    public function currentUser(Request $request)
    {
        $fields = $request->query->all('fields');
        $user = $this->getUser();
        $permissions = $this->em->getRepository(PlanRolePermission::class)->getUserPermissions($user);
        $user->setPermissions($permissions);

        return $this->viewItem(
            $this->getUser(),
            array_merge(
                User::DEFAULT_DISPLAY_VALUES,
                [
                    'updatedBy',
                    'permissions',
                    'plan',
                    Setting::DIGITAL_FORM,
                    'keyContactId',
                    Setting::DATE_FORMAT,
                    'resellerId',
                    'team.clientStatus',
//                    'team.blockedBillingAt',
                    'team.options',
                    Setting::USER_TERMS_ACCEPTANCE,
                    'billingPlanId'
                ],
                $fields
            )
        );
    }

    #[Route('/set-mobile-device', methods: ['POST'])]
    public function setMobileDevice(Request $request)
    {
        try {
            $user = $this->getUser();
            $this->denyAccessUnlessGranted(Permission::SET_MOBILE_DEVICE, User::class);

            $mobileDevice = $this->mobileDeviceService->setMobileDevice($request->request->all(), $user);

            return $this->viewItem($mobileDevice);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/get-mobile-device', methods: ['GET'])]
    public function getMobileDevice(Request $request)
    {
        $mobileDeviceId = $request->query->get('deviceId');

        $mobileDevice = $this->em->getRepository(MobileDevice::class)
            ->findOneBy(['deviceId' => $mobileDeviceId]);

        if (!$mobileDevice) {
            return $this->viewItem(['loginWithId' => false]);
        }

        $loginWithIdSetting = $mobileDevice->getTeam()->getSettingsByName(Setting::LOGIN_WITH_ID);
        $loginWithIdValue = $loginWithIdSetting ? (bool)$loginWithIdSetting->getValue() : false;

        if (!$loginWithIdValue) {
            $mobileDevice->setLoginWithId(false);
        }

        return $this->viewItem($mobileDevice);
    }

    #[Route('/set-mobile-device-token', methods: ['POST'])]
    public function setMobileDeviceToken(Request $request)
    {
        $user = $this->getUser();

        /** @var NotificationMobileDeviceService $mobileDevice */
        $mobileDevice = $this->notificationMobileDeviceService->setMobileDevice($request->request->all(), $user);

        return $this->viewItem($mobileDevice);
    }

    #[Route('/check-driver-id', methods: ['GET'])]
    public function checkDriverId(Request $request)
    {
        $driverId = $request->query->get('driverId');
        $teamId = $request->query->get('teamId');
        $userId = $request->query->get('userId');

        $team = $this->em->getRepository(Team::class)->find($teamId);
        $user = $this->em->getRepository(User::class)
            ->findOneBy(['driverId' => $driverId, 'team' => $team]);

        if ($user && (int)$user->getId() === (int)$userId) {
            $isUnique = true;
        } else {
            $isUnique = !(bool)$user;
        }

        return $this->viewItem(['isUnique' => $isUnique]);
    }

    #[Route('/logout', methods: ['POST'])]
    public function logout(Request $request)
    {
        try {
//            $token = $this->tokenStorage->getToken();
//            $this->authService->logout($token);
            $token = $this->tokenStorage->getToken()->getCredentials();
            $data = [
                'status' => 'ok',
                'redirectUrl' => $this->getUser()->isSSO()
                    ? $this->generateUrl('saml_logout', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL)
                    : null
            ];
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($data);
    }

    #[Route('/token/refresh', methods: ['POST'])]
    public function refreshToken(Request $request)
    {
        try {
            $refreshToken = $request->request->get('refreshToken') ?? null;
            if ($refreshToken) {
                $user = $this->em->getRepository(User::class)
                    ->findOneBy(['refreshToken' => $refreshToken]);
                if (!$user) {
                    return $this->viewJsonError((new InvalidTokenException())->getMessageKey());
                }
                if ($user->getRefreshToken()) {
                    return $this->viewItem(
                        [
                            'token' => $this->authService->getAuthToken($user),
                            'loginWithId' => $user->canLoginWithId(),
                            'expireAt' => DateHelper::getTokenExpireAtString($this->getParameter('jwt_ttl')),
                            ...$this->authService->getRefreshTokenData($user),
                        ]
                    );
                } else {
                    return $this->viewJsonError((new ExpiredTokenException())->getMessageKey());
                }
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem('', [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/client/{id}/login', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function loginAsClient(Request $request, $id): JsonResponse
    {
        try {
            /** @var Client $client */
            $client = $this->em->getRepository(Client::class)
                ->find($id);
            $this->denyAccessUnlessGranted(Permission::LOGIN_AS_CLIENT, $client);

            $clientUser = $client->getKeyContact();
            if ($clientUser) {
                $token = $this->authService->getAuthToken($clientUser, true);
                $this->notificationDispatcher->dispatch(Event::LOGIN_AS_USER, $clientUser);

                return $this->viewItem(
                    [
                        'token' => $token,
                        'expireAt' => DateHelper::getTokenExpireAtString($this->getParameter('jwt_ttl')),
                        'teamType' => $clientUser->getTeamType(),
                        'email' => $clientUser->getEmail(),
                        ...$this->authService->getRefreshTokenData($clientUser),
                    ]
                );
            } else {
                throw new \InvalidArgumentException();
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/reseller/{id}/login', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function loginAsReseller(Request $request, $id): JsonResponse
    {
        try {
            /** @var Reseller $reseller */
            $reseller = $this->em->getRepository(Reseller::class)->find($id);
            $this->denyAccessUnlessGranted(Permission::LOGIN_AS_RESELLER, $reseller);

            $resellerUser = $reseller->getKeyContact();
            if ($resellerUser) {
                $token = $this->authService->getAuthToken($resellerUser, true);
                $this->notificationDispatcher->dispatch(Event::LOGIN_AS_USER, $resellerUser);

                return $this->viewItem(
                    [
                        'token' => $token,
                        'expireAt' => DateHelper::getTokenExpireAtString($this->getParameter('jwt_ttl')),
                        'teamType' => $resellerUser->getTeamType(),
                        'email' => $resellerUser->getEmail(),
                        ...$this->authService->getRefreshTokenData($resellerUser),
                    ]
                );
            } else {
                throw new \InvalidArgumentException($this->translator->trans('entities.reseller.noKeyContact'));
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/user/{id}/login', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function loginAsUser(Request $request, $id): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->em->getRepository(User::class)->find($id);
            $this->denyAccessUnlessGranted(Permission::LOGIN_AS_USER, User::class);
            $this->denyAccessUnlessGranted(null, $user->getTeam());

            if ($user) {
                $token = $this->authService->getAuthToken($user, true);
                $this->notificationDispatcher->dispatch(Event::LOGIN_AS_USER, $user);

                return $this->viewItem(
                    [
                        'token' => $token,
                        'expireAt' => DateHelper::getTokenExpireAtString($this->getParameter('jwt_ttl')),
                        'teamType' => $user->getTeamType(),
                        'email' => $user->getEmail(),
                        ...$this->authService->getRefreshTokenData($user),
                    ]
                );
            } else {
                throw new \InvalidArgumentException();
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }
}