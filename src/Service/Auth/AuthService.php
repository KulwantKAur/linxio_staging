<?php

namespace App\Service\Auth;

use App\Entity\Setting;
use App\Entity\TokenBlacklist;
use App\Entity\User;
use App\Entity\UserDevice;
use App\Events\User\Login\UserLoginEvent;
use App\Service\BaseService;
use App\Service\Setting\SettingService;
use App\Service\Sms\OtpService;
use App\Service\Sms\SmsService;
use App\Util\DateHelper;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\UserNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AuthService extends BaseService
{
    public const LOGIN_TYPE_SSO = 'sso';

    /**
     * @param User $user
     * @return string|null
     */
    private function getHiddenPhone(User $user): ?string
    {
        if (!empty($user->getPhone())) {
            $phone = trim($user->getPhone(), ' ');

            $firstDigits = preg_replace('~[^\-\s\(\)\+]~', '*', (string) substr($phone, 0, -3));
            $lastDigits = substr($phone, -3);


            return $firstDigits . $lastDigits;
        }

        return null;
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param JWTEncoderInterface $JWTEncoder
     * @param UserPasswordHasherInterface $encoder
     * @param EntityManagerInterface $em
     * @param TranslatorInterface $translator
     * @param OtpService $otpService
     * @param SmsService $smsService
     * @param string $refreshJwtTtl
     * @param int $jwtTtl
     */
    public function __construct(
        private EventDispatcherInterface    $eventDispatcher,
        private JWTEncoderInterface         $JWTEncoder,
        private UserPasswordHasherInterface $encoder,
        private EntityManagerInterface      $em,
        private TranslatorInterface         $translator,
        private OtpService                  $otpService,
        private SmsService                  $smsService,
        private SettingService              $settingService,
        private string                      $refreshJwtTtl,
        private int                         $jwtTtl,
    ) {
    }

    public function getRefreshToken(User $user): string
    {
        if (!$user->getRefreshToken()) {
            $user->generateNewRefreshToken($this->refreshJwtTtl);
            $this->em->flush();
            $this->eventDispatcher->dispatch(new UserLoginEvent($user), UserLoginEvent::EVENT_USER_REFRESH_TOKEN);
        }

        return $user->getRefreshToken();
    }

    public function getRefreshTokenData(User $user): array
    {
        return [
            'refreshToken' => $this->getRefreshToken($user),
            'refreshTokenExpireAt' => DateHelper::formatDate($user->getRefreshTokenExpireAt()),
        ];
    }

    public function login(User $user, Request $request, ?string $type = null): array
    {
        return match ($type) {
            self::LOGIN_TYPE_SSO => $this->loginSSOUser($user, $request),
            default => $this->loginUser($user, $request),
        };
    }

    public function loginUser(User $user, Request $request): array
    {
        if ($user->isSSO() && !$user->getSSOIntegrationData()?->isAllowDirectLogin()) {
            throw new BadCredentialsException($this->translator->trans('auth.user.login_restricted'));
        }

        if ($user->getStatus() === User::STATUS_BLOCKED_OVERDUE) {
            throw new BadCredentialsException($this->translator->trans('auth.user.blocked_account_access'));
        }
        
        if ($user->isInClientTeam() && $user->getClient()->isBlockedOverdue()) {
            throw new BadCredentialsException($this->translator->trans('auth.user.blocked_account_access'));
        }

        $deviceId = $request->request->get('deviceId');
        $domain = $request->request->get('domain', null);

        if ($domain && $user->isDriverClient()) {
            $disallowDriverLoginWebapp = $user->getTeam()->getSettingsByName(Setting::DISALLOW_DRIVER_LOGIN_WEBAPP);
            if ($disallowDriverLoginWebapp && (bool) $disallowDriverLoginWebapp->getValue()) {
                throw new BadCredentialsException($this->translator->trans('auth.user.login_restricted'));
            }
        }

        $isValid = $this->encoder->isPasswordValid($user, $request->request->get('password'));

        if (!$isValid) {
            throw new BadCredentialsException($this->translator->trans('auth.invalid_credentials'));
        }
        if ($user->isDeleted()) {
            throw new BadCredentialsException($this->translator->trans('auth.user.deleted'));
        }
        if ($user->isInClientTeam()
            && ($user->getClient()->isClosed() || $user->getClient()->isBlocked())) {
            throw new BadCredentialsException($this->translator->trans('entities.client.blocked'));
        }

        if ($user->isBlocked()) {
            return [
                'blocked' => true,
                'message' => $user->getBlockingMessage(),
                'teamType' => $user->getTeamType()
            ];
        }
        if (!$user->isPhoneVerified() && $user->is2FAEnabled()) {
            return [
                'isPhoneVerified' => $user->isPhoneVerified(),
                'verifyToken' => $user->getVerifyToken(),
                'phone' => $user->getPhone(),
                'teamType' => $user->getTeamType()
            ];
        }
        if (!$user->is2FAEnabled()) {
            return [
                'token' => $this->getAuthToken($user),
                'loginWithId' => $user->canLoginWithId(),
                'expireAt' => DateHelper::getTokenExpireAtString($this->jwtTtl),
                'otp_required' => false,
                'teamType' => $user->getTeamType(),
                'roleId' => $user->getRole()->getId(),
                ...$this->getRefreshTokenData($user),
            ];
        }

        if ($deviceId) {
            $userDevice = $this->em->getRepository(UserDevice::class)->findOneBy(
                [
                    'user' => $user,
                    'deviceId' => $deviceId
                ]
            );
            if ($userDevice) {
                return [
                    'token' => $this->getAuthToken($userDevice->getUser()),
                    'loginWithId' => $user->canLoginWithId(),
                    'expireAt' => DateHelper::getTokenExpireAtString($this->jwtTtl),
                    'otp_required' => false,
                    'teamType' => $user->getTeamType(),
                    'roleId' => $user->getRole()->getId(),
                    ...$this->getRefreshTokenData($user),
                ];
            }
        }

        $otpCode = $this->otpService->createOtp($user->getEmail(), $user);
        $this->smsService->send(
            $user->getPhone(),
            $this->translator->trans('auth.otp.your_code_is', ['%code%' => $otpCode]),
            true,
            $user->getTeam()->getSmsName()
        );

        return [
            'otp_required' => true,
            'phone' => $this->getHiddenPhone($user),
            'teamType' => $user->getTeamType()
        ];
    }

    /**
     * @param User $user
     * @param Request $request
     * @return array
     * @throws \Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException
     */
    public function loginSSOUser(User $user, Request $request): array
    {
        $domain = $request->request->get('domain', null);

        if ($domain && $user->isDriverClient()) {
            $disallowDriverLoginWebapp = $user->getTeam()->getSettingsByName(Setting::DISALLOW_DRIVER_LOGIN_WEBAPP);

            if ($disallowDriverLoginWebapp && (bool) $disallowDriverLoginWebapp->getValue()) {
                throw new BadCredentialsException($this->translator->trans('auth.user.login_restricted'));
            }
        }
        if ($user->isDeleted()) {
            throw new BadCredentialsException($this->translator->trans('auth.user.deleted'));
        }
        if ($user->isInClientTeam() && ($user->getClient()->isClosed() || $user->getClient()->isBlocked())) {
            throw new BadCredentialsException($this->translator->trans('entities.client.blocked'));
        }
        
        if ($user->getStatus() === User::STATUS_BLOCKED_OVERDUE) {
            throw new BadCredentialsException($this->translator->trans('auth.user.blocked_account_access'));
        }
        
        if ($user->isInClientTeam() && $user->getClient()->isBlockedOverdue()) {
            throw new BadCredentialsException($this->translator->trans('auth.user.blocked_account_access'));
        }
        
        if ($user->isBlocked()) {
            return [
                'blocked' => true,
                'message' => $user->getBlockingMessage(),
                'teamType' => $user->getTeamType()
            ];
        }

        return [
            'token' => $this->getAuthToken($user),
            'expireAt' => DateHelper::getTokenExpireAtString($this->jwtTtl),
            'teamType' => $user->getTeamType(),
            'roleId' => $user->getRole()->getId(),
            ...$this->getRefreshTokenData($user),
        ];
    }

    public function getAuthToken(User $user, bool $loginAs = false): string
    {
        $token = $this->JWTEncoder->encode([
            'email' => $user->getEmail(),
            'exp' => time() + $this->jwtTtl,
            'sub' => $user->getId(),
        ]);

        if (!$loginAs) {
            $this->eventDispatcher->dispatch(new UserLoginEvent($user), UserLoginEvent::EVENT_USER_LOGIN);
        }

        return $token;
    }

    /**
     * @param string|null $token
     * @return User
     * @throws \Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException
     */
    public function getUserByToken(?string $token): User
    {
        if (!$token) {
            throw new AccessDeniedException('Token is empty');
        }

        $tokenData = $this->JWTEncoder->decode($token);

        if (!$tokenData || !isset($tokenData['email'])) {
            throw new AccessDeniedException('Token is invalid');
        }

        $userEmail = $tokenData['email'];
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $userEmail]);

        if (!$user) {
            throw new UserNotFoundException('email', $userEmail);
        }

        return $user;
    }

    /**
     * @param string $token
     * @return string
     * @throws \Exception
     */
    public function logoutByStringToken(string $token): string
    {
        $tokenData = $this->JWTEncoder->decode($token);
        $expiredAt = (new \DateTime())->setTimestamp($tokenData['exp']);
        $tokenBlackListItem = new TokenBlacklist(['token' => $token, 'expiredAt' => $expiredAt]);
        $this->em->persist($tokenBlackListItem);
        $this->em->flush();

        return $tokenBlackListItem->getToken();
    }

    /**
     * @param TokenInterface $token
     * @return string
     * @throws \Exception
     */
    public function logoutByTokenInterface(TokenInterface $token): string
    {
        $tokenData = $this->JWTEncoder->decode($token->getCredentials());
        $expiredAt = (new \DateTime())->setTimestamp($tokenData['exp']);
        $tokenBlackListItem = new TokenBlacklist(['token' => $token->getCredentials(), 'expiredAt' => $expiredAt]);
        $this->em->persist($tokenBlackListItem);
        $this->em->flush();

        return $tokenBlackListItem->getToken();
    }
}