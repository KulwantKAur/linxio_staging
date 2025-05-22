<?php

namespace App\Service\User;

use App\Entity\ResetPassword;
use App\Entity\User;
use App\Exceptions\ValidationException;
use App\Mailer\MailSender;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ResetPasswordService
{
    private $resetPasswordTokenTtl;

    public function __construct(
        int $resetPasswordTokenTtl,
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
        private readonly UserService $userService,
        private readonly MailSender $mailSender,
    ) {
        $this->resetPasswordTokenTtl = $resetPasswordTokenTtl;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function generateToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    /**
     * @param $user
     * @return ResetPassword|null
     */
    public function findByUser($user)
    {
        return $this->em->getRepository(ResetPassword::class)->findOneByUser($user);
    }

    /**
     * @param $token
     * @return ResetPassword
     * @throws \Exception
     */
    public function findByToken($token): ResetPassword
    {
        /** @var ResetPassword|null $resetPassword */
        $resetPassword = $this->em->getRepository(ResetPassword::class)->findOneByToken($token);

        if (!$resetPassword) {
            throw new NotFoundHttpException($this->translator->trans('auth.reset_password.not_found'));
        }

        if ($this->checkExpiredToken($resetPassword)) {
            throw (new ValidationException())->setErrors(['token' => $this->translator->trans('auth.reset_password.expired')]);
        }

        return $resetPassword;
    }

    /**
     * @param $token
     * @return bool
     * @throws \Exception
     */
    public function isTokenValid($token): bool
    {
        /** @var ResetPassword|null $resetPassword */
        $resetPassword = $this->em->getRepository(ResetPassword::class)->findOneByToken($token);

        if (!$resetPassword) {
            return false;
        }

        if ($this->checkExpiredToken($resetPassword)) {
            return false;
        }

        return true;
    }

    /**
     * @param $user
     * @throws \Exception
     */
    public function validateExistingRequests($user)
    {
//        $resetPassword = $this->findByUser($user);
//
//        if ($resetPassword) {
//            if (!$this->checkExpiredToken($resetPassword)) {
//                throw new UnprocessableEntityHttpException(
//                    $this->translator->trans('auth.reset_password.already_requested')
//                );
//            }
//        }

        $this->em->getRepository(ResetPassword::class)->invalidateAllForUser($user);
    }

    /**
     * @param ResetPassword $resetPassword
     * @return bool
     * @throws \Exception
     */
    private function checkExpiredToken(ResetPassword $resetPassword): bool
    {
        $resetPasswordCreatedAt = $resetPassword->getCreatedAt()->getTimestamp();

        return $resetPasswordCreatedAt + $this->resetPasswordTokenTtl < (new \DateTime())->getTimestamp();
    }

    /**
     * @param User $user
     * @param $token
     * @return ResetPassword
     */
    public function createFromUser(User $user, $token): ResetPassword
    {
        $resetPassword = new ResetPassword();
        $resetPassword->setToken($token);
        $resetPassword->setEmail($user->getEmail());
        $resetPassword->setUser($user);

        $this->em->persist($resetPassword);
        $this->em->flush();

        return $resetPassword;
    }

    /**
     * @param ResetPassword $resetPassword
     * @return ResetPassword
     * @throws \Exception
     */
    public function verify($resetPassword): ResetPassword
    {
        $resetPassword->setVerifiedAt(new \DateTime());
        $this->em->flush();

        return $resetPassword;
    }

    public function resetPassword(?string $email)
    {
        $user = $this->userService->findUserByEmail($email);
        $this->validateExistingRequests($user);
        $token = $this->generateToken();
        $resetPassword = $this->createFromUser($user, $token);
        $this->mailSender->resetPassword($user, $token, $resetPassword, $this->resetPasswordTokenTtl);
    }
}