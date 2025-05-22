<?php

namespace App\Service\User;


use App\Entity\User;
use App\Exceptions\ValidationException;
use App\Service\Sms\OtpService;
use App\Service\Sms\SmsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class VerificationService
{
    private $em;
    private $translator;
    private $smsService;
    private $otpService;
    private $verifyTokenTtl;

    public function __construct(
        TranslatorInterface $translator,
        EntityManagerInterface $em,
        SmsService $smsService,
        OtpService $otpService,
        int $verifyTokenTtl
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->smsService = $smsService;
        $this->otpService = $otpService;
        $this->verifyTokenTtl = $verifyTokenTtl;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public static function generateVerifyToken()
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * @param array $fields
     * @return mixed
     * @throws ValidationException
     */
    public function sendVerificationCode(array $fields)
    {
        $this->validateVerificationCodeParams($fields, ['token', 'phone']);
        $token = $fields['token'];
        $phone = $fields['phone'];
        /** @var User $user */
        $user = $this->em->getRepository(User::class)->findOneBy(['verifyToken' => $token]);
        $user->setPhone($phone);
        $this->em->flush();
        $code = $this->otpService->createOtpPhone($phone, $user);
        $this->smsService->send(
            $user->getPhone(),
            $this->translator->trans('auth.otp.your_code_is', ['%code%' => $code]),
            true,
            $user->getTeam()->getSmsName()
        );

        return $user;
    }

    /**
     * @param array $fields
     * @return User
     * @throws ValidationException
     */
    public function verifyPhone(array $fields)
    {
        $this->validateVerificationCodeParams($fields, ['code', 'phone']);
        $code = $fields['code'];
        $phone = $fields['phone'];
        $otp = $this->otpService->findOtpByPhone($phone, $code);
        $user = $otp->getUser();
        $this->otpService->verify($otp);
        $user->verifyPhone();
        $this->em->flush();

        return $user;
    }

    /**
     * @param array $fields
     * @param array $params
     * @return bool
     */
    public function validateVerificationCodeParams(array $fields, array $params): bool
    {
        $errors = [];
        foreach ($params as $param) {
            if (!isset($fields[$param]) || !$fields[$param]) {
                $errors[$param] = ['required' => $this->translator->trans('validation.errors.field.required')];
            }
        }
        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }

        return true;
    }

    /**
     * @param string $token
     * @return mixed
     */
    public function getUserPhoneByVerifyToken(string $token)
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['verifyToken' => $token]);
        return ['phone' => $user->getPhone()];
    }

    /**
     * @param $token
     * @return bool
     * @throws \Exception
     */
    public function isVerifyTokenValid($token): bool
    {
        /** @var User|null $user */
        $user = $this->em->getRepository(User::class)->findOneBy(['verifyToken' => $token]);


        if (!$user) {
            return false;
        }

        if (!empty($user->getPassword())) {
            return false;
        }

        if ($user->getCreatedAt()->getTimestamp() + $this->verifyTokenTtl < (new \DateTime())->getTimestamp()) {
            return false;
        }

        return true;
    }

}