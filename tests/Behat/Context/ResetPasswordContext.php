<?php

namespace App\Tests\Behat\Context;

use App\Entity\ResetPassword;

/**
 * Defines application features from the specific context.
 */
class ResetPasswordContext extends BasicContext
{
    /**
     * @When I want to request new password :email
     * @param $email
     * @return ResetPasswordContext
     */
    public function iWantToRequestNewPassword($email)
    {
        return $this->post('/api/password/request', [
            'email' => $email
        ]);
    }

    /**
     * @When I want to request new password with non-existing email :email
     */
    public function iWantToRequestNewPasswordWithNonExistingEmail($email)
    {
        return $this->post('/api/password/request', [
            'email' => $email
        ]);
    }

    /**
     * @When I want to reset password :email :password
     */
    public function iWantToResetPassword($email, $password)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $resetPassword = $em->getRepository(ResetPassword::class)->findOneBy(['email' => $email]);

        return $this->post('/api/password/reset', [
            'token' => $resetPassword->getToken(),
            'password' => $password
        ]);
    }
    /**
     * @When I want check reset token :email
     */
    public function iWantCheckResetToken($email)
    {
        $resetPassword = $this->getEntityManager()->getRepository(ResetPassword::class)->findOneBy(['email' => $email]);

        return $this->post('/api/password/check-reset', [
            'token' => $resetPassword->getToken(),
        ]);
    }

    /**
     * @When I want to reset password with token :email
     */
    public function iWantToExpiredToken($email)
    {
        /** @var ResetPassword $resetPassword */
        $resetPassword = $this->getEntityManager()->getRepository(ResetPassword::class)->findOneBy(['email' => $email]);

        $resetPassword->setCreatedAt((new \DateTime())->modify('-1 day'));

        $this->getEntityManager()->flush();
    }

    /**
     * @When I want login :email :password
     */
    public function iWantLogin($email, $password)
    {
        $data = ['email' => $email, 'password' => $password];
        $response = $this->post('/api/login', $data);

        return $response;
    }
}
