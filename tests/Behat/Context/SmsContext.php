<?php

namespace App\Tests\Behat\Context;

use App\Entity\Otp;
use App\Entity\Sms;
use App\Enums\SmsStatuses;
use App\Service\Sms\Plivo\Plivo;
use Behatch\Asserter;

/**
 * Defines application features from the specific context.
 */
class SmsContext extends UsersContext
{
    use Asserter;

    /**
     * @When I want to receive callback with sms status
     */
    public function iWantToReceiveCallback()
    {
        return $this->post('/api/sms/inbound/status-callback', $this->fillData);
    }

    /**
     * @When I want to generate sms
     */
    public function iWantToGenerateSms()
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $sms = new Sms();
        $sms->setPhoneFrom($this->fillData['From']);
        $sms->setPhoneTo($this->fillData['To']);
        $sms->setStatus(SmsStatuses::PENDING);
        $sms->setMessage('test message');
        $sms->setMessageUuid($this->fillData['MessageUUID']);

        $em->persist($sms);
        $em->flush();

        return $this->get('/api/sms/' . $sms->getId());
    }

    /**
     * @When Sms service is ready to send sms
     */
    public function smsServiceIsReady()
    {
        $mock = \Mockery::mock(Plivo::class);
        $mock->shouldReceive('send')
            ->andReturn(true)
            ->once();

        $this->getContainer()->set('app.sms_service.plivo', $mock);
    }

    /**
     * @When I want to find otp by email :email
     */
    public function iWantToFindOtpByEmail($email)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $otp = $em->getRepository(Otp::class)->findOneBy(['email' => $email]);

        $this->assertTrue($otp);
    }
}
