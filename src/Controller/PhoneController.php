<?php

namespace App\Controller;

use App\Entity\User;
use App\Events\User\Login\UserLoginEvent;
use App\Service\Auth\AuthService;
use App\Service\User\UserService;
use App\Service\User\VerificationService;
use App\Util\DateHelper;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PhoneController extends BaseController
{
    private $verificationService;
    private $userService;
    private $JWTEncoder;
    private $eventDispatcher;

    public function __construct(
        private readonly AuthService $authService,
        VerificationService $verificationService,
        UserService $userService,
        JWTEncoderInterface $JWTEncoder,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->verificationService = $verificationService;
        $this->userService = $userService;
        $this->JWTEncoder = $JWTEncoder;
        $this->eventDispatcher = $eventDispatcher;
    }

    #[Route('/phone/code', methods: ['POST'])]
    public function sendVerifyCode(Request $request)
    {
        try {
            $user = $this->verificationService->sendVerificationCode($request->request->all());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($user, User::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/phone/verify', methods: ['POST'])]
    public function verifyPhone(Request $request)
    {
        try {
            $user = $this->verificationService->verifyPhone($request->request->all());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
        if ($user->getStatus() !== User::STATUS_NEW) {
            return $this->viewItem(
                [
                    'token' => $this->getAuthToken($user),
                    'loginWithId' => $user->canLoginWithId(),
                    'expireAt' => DateHelper::getTokenExpireAtString($this->getParameter('jwt_ttl')),
                    'teamType' => $user->getTeamType(),
                    ...$this->authService->getRefreshTokenData($user),
                ]
            );
        }

        return $this->viewItem($user, array_merge(User::DEFAULT_DISPLAY_VALUES, ['verifyToken']));
    }

    #[Route('/phone', methods: ['POST'])]
    public function getUserPhoneByVerifyToken(Request $request)
    {
        try {
            $phone = $this->verificationService->getUserPhoneByVerifyToken($request->request->get('token'));
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($phone);
    }

    /**
     * @param $user
     * @return string
     */
    private function getAuthToken($user): string
    {
        $token = $this->JWTEncoder
            ->encode([
                'email' => $user->getEmail(),
                'exp' => time() + $this->getParameter('jwt_ttl')
            ]);

        $this->eventDispatcher->dispatch(new UserLoginEvent($user), UserLoginEvent::EVENT_USER_LOGIN);

        return $token;
    }
}
