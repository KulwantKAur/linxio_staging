<?php

namespace App\Service\User;

use App\Entity\User;
use App\Exceptions\ValidationException;
use Doctrine\ORM\EntityManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordService
{
    public const SYMBOLS = '~!^(){}<>%@#&*+=_-';

    private $encoder;
    private $em;

    public function __construct(UserPasswordHasherInterface $encoder, EntityManager $em)
    {
        $this->encoder = $encoder;
        $this->em = $em;
    }

    /**
     * @param string $password
     * @param $translator
     * @return bool
     * @throws \Exception
     */
    public static function validatePassword(string $password, $translator): bool
    {
        $errors = [];
        if (!preg_match('/[A-Z]+/', $password)) {
            $errors['password'] = ['uppercase' => $translator->trans('validation.errors.password.uppercase')];
        }
        if (!preg_match('/[a-z]+/', $password)) {
            $errors['password'] = ['lowercase' => $translator->trans('validation.errors.password.lowercase')];
        }
        if (!preg_match("/[" . self::SYMBOLS . "]+/", $password)) {
            $errors['password'] = ['symbols' => $translator->trans('validation.errors.password.symbols')];
        }
        if (!preg_match('/[0-9]+/', $password)) {
            $errors['password'] = ['digits' => $translator->trans('validation.errors.password.digits')];
        }
        if (strlen($password) < 8) {
            $errors['password'] = ['min_length' => $translator->trans('validation.errors.password.min_length')];
        }
        if (!preg_match('/^(?=.*[A-Z]+)(?=.*[~!^(){}<>%@#&*+=_-]+)(?=.*[0-9]+)(?=.*[a-z]+).{8,}$/', $password)) {
            $errors['password'] = ['weak' => $translator->trans('validation.errors.password.weak')];
        }
        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }

        return true;
    }

    /**
     * @return string
     */
    public static function generatePassword()
    {
        $lowerLetters = range('a', 'z');
        $upperLetters = range('A', 'Z');
        $digits = range(0, 9);
        $symbols = str_split(self::SYMBOLS);
        return self::randomStrFromArrays([$lowerLetters, $upperLetters, $digits, $symbols, $upperLetters])
            . self::randomStrFromArrays([$lowerLetters, $upperLetters, $digits, $symbols, $upperLetters]);
    }

    /**
     * @param array $arrays
     * @return string
     */
    private static function randomStrFromArrays(array $arrays): string
    {
        $str = [];
        shuffle($arrays);
        foreach ($arrays as $array) {
            $str[] = self::arrayRandomItem($array);
        }
        shuffle($str);
        return implode($str);
    }

    /**
     * @param $array
     * @return mixed
     */
    private static function arrayRandomItem($array)
    {
        return $array[array_rand($array)];
    }

    /**
     * @param $password
     * @param User $user
     * @return bool
     */
    public function setUserPassword($password, User $user)
    {
        $user->setPassword($this->encoder->hashPassword($user, $password));
        $this->em->flush();
        return true;
    }
}