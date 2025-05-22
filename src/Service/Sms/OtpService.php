<?php

namespace App\Service\Sms;

use App\Entity\Otp;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Contracts\Translation\TranslatorInterface;

class OtpService
{
    private $em;
    private $env;
    private $translator;
    private $otpTtl;
    private $smsEnabled;

    /**
     * SmsService constructor.
     * @param EntityManagerInterface $em
     * @param $env
     * @param $otpTtl
     * @param TranslatorInterface $translator
     * @param $smsEnabled
     */
    public function __construct(
        EntityManagerInterface $em,
        $env,
        $otpTtl,
        TranslatorInterface $translator,
        $smsEnabled
    ) {
        $this->em = $em;
        $this->env = $env;
        $this->otpTtl = $otpTtl;
        $this->translator = $translator;
        $this->smsEnabled = $smsEnabled === 'true' ? true : false;
    }

    /**
     * @param $email
     * @param User $user
     * @return int
     */
    public function createOtp($email, $user = null)
    {
        if ($user) {
            $this->em->getRepository(Otp::class)->invalidateAllForUserId($user->getId());
        }

        $code = ($this->env == 'prod') && $this->smsEnabled ? mt_rand(1000, 9999) : 1234;

        $otp = new Otp();
        $otp->setEmail($email);
        $otp->setUser($user);
        $otp->setCode($code);

        $this->em->persist($otp);
        $this->em->flush();

        return $code;
    }

    /**
     * @param $phone
     * @param User $user
     * @return int
     */
    public function createOtpPhone($phone, $user = null)
    {
        if ($user) {
            $this->em->getRepository(Otp::class)->invalidateAllForUserId($user->getId());
        }

        $code = ($this->env == 'prod') && $this->smsEnabled ? mt_rand(1000, 9999) : 1234;

        $otp = new Otp();
        $otp->setPhone($phone);
        $otp->setUser($user);
        $otp->setCode($code);

        $this->em->persist($otp);
        $this->em->flush();

        return $code;
    }

    /**
     * @param $phone
     * @param $code
     * @return null|Otp
     */
    public function findOtpByPhone($phone, $code)
    {
        $otp = $this->em->getRepository(Otp::class)->findOneByPhone($phone);

        if (!$otp) {
            throw new NotFoundHttpException($this->translator->trans('auth.otp.not_found_for_phone'));
        }

        if ($otp->getCode() != $code) {
            throw new BadRequestHttpException($this->translator->trans('auth.otp.code_is_wrong'));
        }

        return $otp;
    }

    /**
     * @param $email
     * @param $code
     * @return null|Otp
     */
    public function findOtpByEmail($email, $code)
    {
        $otp = $this->em->getRepository(Otp::class)->findOneByEmail($email);

        if (!$otp) {
            throw new NotFoundHttpException($this->translator->trans('auth.otp.not_found_for_email'));
        }

        if ($otp->getCode() != $code) {
            throw new BadRequestHttpException($this->translator->trans('auth.otp.code_is_wrong'));
        }

        return $otp;
    }

    /**
     * @param $otp
     * @return null|object
     */
    public function verify(Otp $otp)
    {
        $otp->setVerifiedAt(new \DateTime());
        $this->em->flush();
        $otpCreatedAt = $otp->getCreatedAt()->getTimestamp();

        if ($otpCreatedAt + $this->otpTtl < (new \DateTime())->getTimestamp()) {
            throw new CredentialsExpiredException($this->translator->trans('auth.otp.expired'));
        }

        return $otp;
    }
}