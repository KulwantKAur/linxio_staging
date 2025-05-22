<?php

namespace App\Service\Sms;

use App\Entity\Sms;
use App\Service\Sms\Interfaces\SmsSendingInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class SmsService
{
    private $service;
    private $env;
    private $em;
    private $logger;
    private $smsEnabled;

    /**
     * SmsService constructor.
     * @param SmsSendingInterface $service
     * @param EntityManagerInterface $em
     * @param LoggerInterface $logger
     * @param $env
     * @param $smsEnabled
     */
    public function __construct(
        SmsSendingInterface $service,
        EntityManagerInterface $em,
        LoggerInterface $logger,
        $env,
        $smsEnabled
    ) {
        $this->em = $em;
        $this->env = $env;
        $this->service = $service;
        $this->logger = $logger;
        $this->smsEnabled = $smsEnabled === 'true' ? true : false;
    }

    /**
     * @param $id
     * @return Sms
     */
    public function get($id): Sms
    {
        return $this->em->getRepository(Sms::class)->findOneBy(['id' => $id]);
    }

    /**
     * @param string $to
     * @param string $text
     * @param bool $is2FA
     * @param null $from
     * @return array|object
     */
    public function send($to, $text, $is2FA = false, $from = null)
    {
        try {
            if ($this->env == 'prod' && $this->smsEnabled) {
                return $this->service->send($to, $text, $is2FA, $from);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['code' => $e->getCode()]);
        }

        return ['message' => 1234];
    }

    /**
     * @param $data
     * @return \App\Entity\Sms
     */
    public function update($data)
    {
        return $this->service->update($data);
    }
}