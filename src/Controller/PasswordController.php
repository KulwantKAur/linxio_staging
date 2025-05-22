<?php

namespace App\Controller;

use App\Entity\User;
use App\Mailer\MailSender;
use App\Service\User\PasswordService;
use App\Service\User\ResetPasswordService;
use App\Service\User\UserService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/password')]
class PasswordController extends BaseController
{
    private $userService;
    private $translator;
    private $resetPasswordService;
    private $passwordService;
    private $mailSender;

    public function __construct(
        UserService $userService,
        TranslatorInterface $translator,
        ResetPasswordService $resetPasswordService,
        PasswordService $passwordService,
        MailSender $mailSender
    ) {
        $this->userService = $userService;
        $this->translator = $translator;
        $this->resetPasswordService = $resetPasswordService;
        $this->passwordService = $passwordService;
        $this->mailSender = $mailSender;
    }

    #[Route('/request', methods: ['POST'])]
    public function requestAction(Request $request)
    {
        $email = $request->request->get('email');
        $this->resetPasswordService->resetPassword($email);

        return $this->viewItem(['mail_sent' => true]);
    }

    #[Route('/reset', methods: ['POST'])]
    public function verifyAction(Request $request)
    {
        try {
            $token = $request->request->get('token');
            $password = $request->request->get('password');
            $resetPassword = $this->resetPasswordService->findByToken($token);
            $user = $resetPassword->getUser();
            $user = $this->userService->updatePassword($user, $password);
            $this->resetPasswordService->verify($resetPassword);
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($user);
    }

    #[Route('/check-reset', methods: ['POST'])]
    public function checkResetTokenAction(Request $request)
    {
        try {
            return $this->viewItem(
                [
                    'tokenValid' => $this->resetPasswordService->isTokenValid($request->request->get('token'))
                ]
            );
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/set', methods: ['POST'])]
    public function setPassword(Request $request)
    {
        try {
            if ($this->getUser() instanceof User) {
                throw new AccessDeniedHttpException($this->translator->trans('general.access_denied'));
            }

            $token = $request->request->get('token');
            $password = $request->request->get('password');
            $user = $this->userService->findUserByVerifyToken($token);
            $this->passwordService->setUserPassword($password, $user);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($user);
    }
}